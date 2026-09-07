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

/**
 * Locates a safe HTML entry point within block file storage.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entrypoint_locator {
    /**
     * Finds the configured entry point or an unambiguous index.html fallback.
     *
     * @param context_block $context block context
     * @param string $configured configured relative path
     * @return array{filepath:string,filename:string}
     */
    public static function locate(context_block $context, string $configured): array {
        $configured = self::normalise_path($configured);
        $pathinfo = pathinfo($configured);
        $filepath = empty($pathinfo['dirname']) || $pathinfo['dirname'] === '.'
            ? '/'
            : '/' . trim($pathinfo['dirname'], '/') . '/';
        $filename = $pathinfo['basename'] ?? '';

        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'block_interactembed', 'content', 0, $filepath, $filename);
        if ($file && !$file->is_directory() && self::is_html($filename)) {
            return ['filepath' => $filepath, 'filename' => $filename];
        }

        $matches = [];
        $files = $fs->get_area_files(
            $context->id,
            'block_interactembed',
            'content',
            0,
            'filepath, filename',
            false
        );
        foreach ($files as $candidate) {
            if (strtolower($candidate->get_filename()) === 'index.html') {
                $matches[] = $candidate;
            }
        }
        if (count($matches) === 1) {
            return ['filepath' => $matches[0]->get_filepath(), 'filename' => $matches[0]->get_filename()];
        }

        throw new \moodle_exception('packageentrymissing', 'block_interactembed');
    }

    /**
     * Normalises a user-provided relative file path.
     *
     * @param string $path path
     * @return string
     */
    public static function normalise_path(string $path): string {
        $path = str_replace('\\', '/', trim($path));
        if (
            $path === '' ||
            substr($path, 0, 1) === '/' ||
            preg_match('~^[A-Za-z]:/~', $path) ||
            strpos($path, "\0") !== false ||
            preg_match('~(^|/)\.\.?(/|$)~', $path) ||
            strpos($path, '//') !== false ||
            !self::is_html($path)
        ) {
            throw new \moodle_exception('packageentryinvalid', 'block_interactembed');
        }
        return $path;
    }

    /**
     * Checks whether a filename has an HTML extension.
     *
     * @param string $filename filename
     * @return bool
     */
    private static function is_html(string $filename): bool {
        return in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), ['html', 'htm'], true);
    }
}
