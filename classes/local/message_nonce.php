<?php
// This file is part of Moodle - https://moodle.org/
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

namespace block_interactembed\local;

/**
 * Creates channel-binding nonces for same-Moodle embed messages.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class message_nonce {
    /**
     * Creates a 128-bit nonce represented as lowercase hexadecimal.
     *
     * @return string Message nonce.
     */
    public static function create(): string {
        return bin2hex(random_bytes(16));
    }

    /**
     * Checks the exact public nonce representation.
     *
     * @param string $nonce Candidate nonce.
     * @return bool Whether it is valid.
     */
    public static function is_valid(string $nonce): bool {
        return preg_match('/^[a-f0-9]{32}$/D', $nonce) === 1;
    }
}
