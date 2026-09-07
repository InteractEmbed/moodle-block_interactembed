<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace block_interactembed\local\package;

/**
 * Validates ZIP metadata before extraction.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class archive_validator {
    /** Maximum number of path components accepted in an archive entry. */
    private const MAX_PATH_DEPTH = 50;

    /**
     * Validates an archive file.
     *
     * @param \stored_file $archive ZIP stored file
     * @return array archive entries
     */
    public static function validate(\stored_file $archive): array {
        self::validate_zip_metadata($archive);

        $packer = get_file_packer('application/zip');
        $entries = $archive->list_files($packer);
        if (!is_array($entries)) {
            throw new \moodle_exception('packageinvalidarchive', 'block_interactembed');
        }

        [$maxfiles, $maxbytes] = self::archive_limits();
        if (count($entries) > $maxfiles) {
            throw new \moodle_exception('packageunsafearchive', 'block_interactembed');
        }

        self::validate_packer_entries($entries, $maxbytes);
        return $entries;
    }

    /**
     * Returns the configured archive limits with safe minimums.
     *
     * @return array{0: int, 1: int} maximum files and expanded bytes
     */
    private static function archive_limits(): array {
        return [
            max(1, (int)(get_config('block_interactembed', 'maxarchivefiles') ?: 5000)),
            max(1048576, (int)(get_config('block_interactembed', 'maxarchivebytes') ?: 209715200)),
        ];
    }

    /**
     * Validates paths and expanded size exposed by Moodle's file packer.
     *
     * @param array $entries archive entries
     * @param int $maxbytes maximum expanded size
     */
    private static function validate_packer_entries(array $entries, int $maxbytes): void {
        $totalbytes = 0;
        foreach ($entries as $entry) {
            $path = str_replace('\\', '/', (string)($entry->original_pathname ?? $entry->pathname));
            $totalbytes += max(0, (int)($entry->size ?? 0));
            if (self::is_unsafe_path($path) || $totalbytes > $maxbytes) {
                throw new \moodle_exception('packageunsafearchive', 'block_interactembed');
            }
        }
    }

    /**
     * Validates one unpacked relative asset path.
     *
     * @param string $path relative path
     */
    public static function validate_path(string $path): void {
        if (self::is_unsafe_path(str_replace('\\', '/', $path))) {
            throw new \moodle_exception('packageunsafefile', 'block_interactembed', '', $path);
        }
    }

    /**
     * Applies ZIP-equivalent limits to an unpacked file collection.
     *
     * @param \stored_file[] $files files to validate
     */
    public static function validate_collection(array $files): void {
        [$maxfiles, $maxbytes] = self::archive_limits();
        if (count($files) > $maxfiles) {
            throw new \moodle_exception('packageunsafearchive', 'block_interactembed');
        }
        $totalbytes = 0;
        foreach ($files as $file) {
            if (!$file instanceof \stored_file || $file->is_directory()) {
                continue;
            }
            $path = ltrim($file->get_filepath(), '/') . $file->get_filename();
            self::validate_path($path);
            $totalbytes += $file->get_filesize();
            if (substr_count($path, '/') + 1 > self::MAX_PATH_DEPTH || $totalbytes > $maxbytes) {
                throw new \moodle_exception('packageunsafearchive', 'block_interactembed');
            }
        }
    }

    /**
     * Detects archive paths that cannot be extracted safely.
     *
     * @param string $path archive entry path
     * @return bool whether the path is unsafe
     */
    private static function is_unsafe_path(string $path): bool {
        if ($path === '' || strpos($path, "\0") !== false || $path[0] === '/') {
            return true;
        }

        return (bool)(
            preg_match('~^[A-Za-z]:/~', $path) ||
            preg_match('~(^|/)\.\.(/|$)~', $path) ||
            preg_match('~(^|/)\.(/|$)~', $path) ||
            str_contains($path, '//') ||
            preg_match('/[\x00-\x1F\x7F]/', $path) ||
            preg_match('~\.zip$~i', rtrim($path, '/'))
        );
    }

    /**
     * Checks ZIP metadata not exposed by Moodle's generic file packer.
     *
     * @param \stored_file $archive ZIP stored file
     */
    private static function validate_zip_metadata(\stored_file $archive): void {
        $pathname = tempnam(make_request_directory(), 'interactembed-zip-');
        if ($pathname === false || !$archive->copy_content_to($pathname)) {
            throw new \moodle_exception('packageinvalidarchive', 'block_interactembed');
        }

        $zip = new \ZipArchive();
        $opened = false;
        try {
            if ($zip->open($pathname) !== true) {
                throw new \moodle_exception('packageinvalidarchive', 'block_interactembed');
            }
            $opened = true;

            self::validate_zip_entries($zip);
        } finally {
            if ($opened) {
                $zip->close();
            }
            if (file_exists($pathname)) {
                unlink($pathname);
            }
        }
    }

    /**
     * Validates metadata for every raw ZIP entry.
     *
     * @param \ZipArchive $zip opened ZIP archive
     */
    private static function validate_zip_entries(\ZipArchive $zip): void {
        $seenpaths = [];
        for ($index = 0; $index < $zip->numFiles; $index++) {
            self::validate_zip_entry($zip, $index, $seenpaths);
        }
    }

    /**
     * Rejects duplicate, over-deep and symbolic-link ZIP entries.
     *
     * @param \ZipArchive $zip opened ZIP archive
     * @param int $index entry index
     * @param array $seenpaths normalised paths already encountered
     */
    private static function validate_zip_entry(\ZipArchive $zip, int $index, array &$seenpaths): void {
        $stat = $zip->statIndex($index, \ZipArchive::FL_UNCHANGED);
        if (!is_array($stat) || !isset($stat['name'])) {
            throw new \moodle_exception('packageinvalidarchive', 'block_interactembed');
        }

        $path = str_replace('\\', '/', (string)$stat['name']);
        $normalised = rtrim($path, '/');
        $depth = $normalised === '' ? 0 : substr_count($normalised, '/') + 1;
        if (
            $depth > self::MAX_PATH_DEPTH ||
            isset($seenpaths[$normalised]) ||
            self::is_unsafe_path($path)
        ) {
            throw new \moodle_exception('packageunsafearchive', 'block_interactembed');
        }
        $seenpaths[$normalised] = true;

        $opsys = 0;
        $attributes = 0;
        if ($zip->getExternalAttributesIndex($index, $opsys, $attributes)) {
            $filetype = ($attributes >> 16) & 0170000;
            if ($filetype === 0120000) {
                throw new \moodle_exception('packageunsafearchive', 'block_interactembed');
            }
        }
    }
}
