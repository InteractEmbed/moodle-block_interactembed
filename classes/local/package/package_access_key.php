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
 * Creates short-lived signed path segments for sandboxed package resources.
 *
 * A sandbox without allow-same-origin does not send the Moodle session with
 * relative CSS, JavaScript and image requests. The signed path grants access
 * only to one block revision for a short period, without exposing the session.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class package_access_key {
    /** Token validity window in seconds. The previous window is also accepted. */
    private const WINDOW = 3600;

    /** Token prefix used to distinguish the virtual path from package folders. */
    private const PREFIX = 'ieak';

    /**
     * Creates a signed virtual path segment.
     *
     * @param int $contextid block context id
     * @param int $revision saved package revision
     * @param int|null $time testable current timestamp
     * @return string signed path segment
     */
    public static function create(int $contextid, int $revision, ?int $time = null): string {
        $slot = (int)floor(($time ?? time()) / self::WINDOW);
        return self::PREFIX . $slot . '-' . self::signature($contextid, $revision, $slot);
    }

    /**
     * Checks whether a path segment has the access-key shape.
     *
     * @param string $segment path segment
     * @return bool
     */
    public static function is_candidate(string $segment): bool {
        return preg_match('/^' . self::PREFIX . '\d+-[a-f0-9]{64}$/', $segment) === 1;
    }

    /**
     * Validates a signed path segment for the current or previous time window.
     *
     * @param string $key submitted key
     * @param int $contextid block context id
     * @param int $revision saved package revision
     * @param int|null $time testable current timestamp
     * @return bool
     */
    public static function validate(string $key, int $contextid, int $revision, ?int $time = null): bool {
        if (!self::is_candidate($key) || !preg_match('/^' . self::PREFIX . '(\d+)-/', $key, $matches)) {
            return false;
        }

        $slot = (int)$matches[1];
        $currentslot = (int)floor(($time ?? time()) / self::WINDOW);
        if ($slot !== $currentslot && $slot !== $currentslot - 1) {
            return false;
        }

        $expected = self::PREFIX . $slot . '-' . self::signature($contextid, $revision, $slot);
        return hash_equals($expected, $key);
    }

    /**
     * Calculates the key signature with Moodle's private site identifier.
     *
     * @param int $contextid block context id
     * @param int $revision saved package revision
     * @param int $slot validity window
     * @return string hexadecimal signature
     */
    private static function signature(int $contextid, int $revision, int $slot): string {
        $secret = (string)get_site_identifier();
        if ($secret === '') {
            throw new \coding_exception('Moodle site identifier is unavailable.');
        }
        return hash_hmac('sha256', $contextid . ':' . $revision . ':' . $slot, $secret);
    }
}
