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

namespace block_interactembed\local\package;

use block_interactembed\local\security\sandbox_policy;

/**
 * Builds and applies package resource response headers.
 *
 * @package   block_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class package_response_policy {
    /**
     * Returns response headers for one package resource.
     *
     * A wildcard is safe here because the endpoint is cookie-free, read-only and
     * protected by a short-lived, revision-scoped signature. It is also required
     * for sandboxed documents whose opaque origins cannot be safely allowlisted
     * by their serialised "null" value.
     *
     * @param string $filename Served filename.
     * @param string $profile Stored sandbox profile.
     * @param bool $signed Whether this is the cookie-free signed endpoint.
     * @return array<string, string> Header names and values.
     */
    public static function headers(string $filename, string $profile, bool $signed): array {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ];
        if ($signed) {
            $headers += [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
                'Cross-Origin-Resource-Policy' => 'cross-origin',
            ];
        }
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($extension, ['html', 'htm'], true)) {
            $headers['Content-Security-Policy'] = sandbox_policy::document_csp($profile);
        }
        return $headers;
    }

    /**
     * Sends package response headers.
     *
     * @param string $filename Served filename.
     * @param string $profile Stored sandbox profile.
     * @param bool $signed Whether this is the cookie-free signed endpoint.
     */
    public static function apply(string $filename, string $profile, bool $signed): void {
        foreach (self::headers($filename, $profile, $signed) as $name => $value) {
            header($name . ': ' . $value, true);
        }
    }
}
