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

use context_block;
use context_user;

/**
 * Validates and expands ZIP package uploads.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class package_importer {
    /**
     * Validates any ZIP in a user's draft area.
     *
     * @param int $draftitemid draft item id
     * @param string $entrypoint configured start file
     */
    public static function validate_draft(int $draftitemid, string $entrypoint): void {
        global $USER;

        $entrypoint = entrypoint_locator::normalise_path($entrypoint);

        $files = get_file_storage()->get_area_files(
            context_user::instance($USER->id)->id,
            'user',
            'draft',
            $draftitemid,
            'id ASC',
            false
        );
        if (empty($files)) {
            throw new \moodle_exception('packagefilesrequired', 'block_interactembed');
        }
        $archive = self::validate_file_collection($files);
        if ($archive !== null) {
            $entries = archive_validator::validate($archive);
            $paths = [];
            foreach ($entries as $entry) {
                if (empty($entry->is_directory)) {
                    $paths[] = str_replace('\\', '/', (string)$entry->pathname);
                }
            }
        } else {
            $paths = [];
            foreach ($files as $file) {
                $path = self::stored_file_path($file);
                archive_validator::validate_path($path);
                $paths[] = $path;
            }
        }
        self::validate_entrypoint($paths, $entrypoint);
    }

    /**
     * Expands a validated ZIP already saved in the block file area.
     *
     * @param context_block $context destination block context
     */
    public static function expand_saved_archive(context_block $context): void {
        global $USER;

        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $context->id,
            'block_interactembed',
            'content',
            0,
            'id ASC',
            false
        );
        $archive = self::validate_file_collection($files);
        if ($archive === null) {
            archive_validator::validate_collection($files);
            foreach ($files as $file) {
                archive_validator::validate_path(self::stored_file_path($file));
            }
            return;
        }

        archive_validator::validate($archive);
        $fs->delete_area_files($context->id, 'block_interactembed', 'packageextract', 0);
        $packer = get_file_packer('application/zip');
        $result = $packer->extract_to_storage(
            $archive,
            $context->id,
            'block_interactembed',
            'packageextract',
            0,
            '/',
            $USER->id
        );
        if (!is_array($result)) {
            $fs->delete_area_files($context->id, 'block_interactembed', 'packageextract', 0);
            throw new \moodle_exception('packageinvalidarchive', 'block_interactembed');
        }

        $extracted = $fs->get_area_files(
            $context->id,
            'block_interactembed',
            'packageextract',
            0,
            'id ASC',
            false
        );
        $fs->delete_area_files($context->id, 'block_interactembed', 'content', 0);
        foreach ($extracted as $file) {
            $fs->create_file_from_storedfile([
                'contextid' => $context->id,
                'component' => 'block_interactembed',
                'filearea' => 'content',
                'itemid' => 0,
            ], $file);
        }
        $fs->delete_area_files($context->id, 'block_interactembed', 'packageextract', 0);
    }

    /**
     * Validates and activates an already unpacked Creator snapshot.
     *
     * @param context_block $context destination block context
     * @param \stored_file[] $sourcefiles source publication files
     * @param string $entrypoint declared entry point
     * @param string $expectedhash Creator API v1 canonical hash
     */
    public static function import_stored_files(
        context_block $context,
        array $sourcefiles,
        string $entrypoint,
        string $expectedhash
    ): void {
        $entrypoint = entrypoint_locator::normalise_path($entrypoint);
        $fs = get_file_storage();
        $itemid = time() + random_int(1, 1000000);
        $paths = [];
        try {
            archive_validator::validate_collection($sourcefiles);
            if (
                !preg_match('/^[a-f0-9]{64}$/', $expectedhash) ||
                    !hash_equals($expectedhash, self::canonical_hash($sourcefiles))
            ) {
                throw new \moodle_exception('packageinvalidarchive', 'block_interactembed');
            }
            foreach ($sourcefiles as $file) {
                if (!$file instanceof \stored_file || $file->is_directory()) {
                    continue;
                }
                $path = self::stored_file_path($file);
                archive_validator::validate_path($path);
                if (isset($paths[$path])) {
                    throw new \moodle_exception('packageinvalidarchive', 'block_interactembed');
                }
                $paths[$path] = true;
                $fs->create_file_from_storedfile([
                    'contextid' => $context->id,
                    'component' => 'block_interactembed',
                    'filearea' => 'packageimport',
                    'itemid' => $itemid,
                    'filepath' => $file->get_filepath(),
                    'filename' => $file->get_filename(),
                ], $file);
            }
            if (!$paths) {
                throw new \moodle_exception('packagefilesrequired', 'block_interactembed');
            }
            self::validate_entrypoint(array_keys($paths), $entrypoint);
            $staged = $fs->get_area_files(
                $context->id,
                'block_interactembed',
                'packageimport',
                $itemid,
                'filepath, filename',
                false
            );
            $fs->delete_area_files($context->id, 'block_interactembed', 'content', 0);
            foreach ($staged as $file) {
                $fs->create_file_from_storedfile([
                    'contextid' => $context->id,
                    'component' => 'block_interactembed',
                    'filearea' => 'content',
                    'itemid' => 0,
                    'filepath' => $file->get_filepath(),
                    'filename' => $file->get_filename(),
                ], $file);
            }
        } finally {
            $fs->delete_area_files($context->id, 'block_interactembed', 'packageimport', $itemid);
        }
    }

    /**
     * Returns the Creator API v1 canonical hash without calling Creator code.
     *
     * @param \stored_file[] $files files to hash
     * @return string lowercase SHA-256
     */
    private static function canonical_hash(array $files): string {
        $records = [];
        foreach ($files as $file) {
            if (!$file instanceof \stored_file || $file->is_directory()) {
                continue;
            }
            $path = self::stored_file_path($file);
            if (isset($records[$path])) {
                throw new \moodle_exception('packageinvalidarchive', 'block_interactembed');
            }
            $records[$path] = $file;
        }
        ksort($records, SORT_STRING);
        $hash = hash_init('sha256');
        foreach ($records as $path => $file) {
            hash_update($hash, $path . "\0" . $file->get_contenthash() . "\0" . $file->get_filesize() . "\0");
        }
        return hash_final($hash);
    }

    /**
     * Ensures ZIP and unpacked upload modes are not mixed.
     *
     * @param \stored_file[] $files stored files
     * @return \stored_file|null archive if present
     */
    private static function validate_file_collection(array $files): ?\stored_file {
        $archives = [];
        foreach ($files as $file) {
            if (strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION)) === 'zip') {
                $archives[] = $file;
            }
        }
        if (count($archives) > 1 || (count($archives) === 1 && count($files) !== 1)) {
            throw new \moodle_exception('packageonlyonearchive', 'block_interactembed');
        }
        if (count($archives) === 1) {
            return $archives[0];
        }
        return null;
    }

    /**
     * Creates a relative path for one stored file.
     *
     * @param \stored_file $file stored file
     * @return string relative path
     */
    private static function stored_file_path(\stored_file $file): string {
        $directory = trim($file->get_filepath(), '/');
        return ($directory === '' ? '' : $directory . '/') . $file->get_filename();
    }

    /**
     * Ensures a configured or unambiguous default entry point exists.
     *
     * @param array $paths package file paths
     * @param string $entrypoint configured path
     */
    private static function validate_entrypoint(array $paths, string $entrypoint): void {
        if (in_array($entrypoint, $paths, true)) {
            return;
        }
        $indexes = array_filter($paths, static function (string $path): bool {
            return strtolower(basename($path)) === 'index.html';
        });
        if (count($indexes) !== 1) {
            throw new \moodle_exception('packageentrymissing', 'block_interactembed');
        }
    }
}
