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

namespace block_interactembed\local\security;

/**
 * Defines the least-privilege sandbox profiles shared by HTML and HTTP policy.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class sandbox_policy {
    /** @var array<string, string> Supported package sandbox profiles. */
    private const PROFILES = [
        'strict' => 'allow-scripts allow-forms allow-presentation',
        'standard' => 'allow-scripts allow-forms allow-popups allow-presentation allow-downloads',
        'trusted' => 'allow-scripts allow-forms allow-popups allow-presentation allow-downloads allow-same-origin',
    ];

    /**
     * Returns a normalised iframe sandbox attribute.
     *
     * Popups remain sandboxed. The escape token is deliberately excluded from
     * every profile because InteractEmbed does not need to create unsandboxed
     * auxiliary browsing contexts.
     *
     * @param string $profile Stored profile name.
     * @return string Sandbox tokens.
     */
    public static function tokens(string $profile): string {
        return self::PROFILES[$profile] ?? self::PROFILES['standard'];
    }

    /**
     * Returns an HTTP CSP for an HTML package entry point.
     *
     * @param string $profile Stored profile name.
     * @return string Content-Security-Policy value.
     */
    public static function document_csp(string $profile): string {
        return 'sandbox ' . self::tokens($profile) . "; frame-ancestors 'self'";
    }
}
