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

/**
 * Height reporting for the same-Moodle embed page.
 *
 * @module     block_interactembed/embed_child
 * @copyright  2026 Michel Cardinal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Reports the embedded player height to a same-origin parent page.
 *
 * @param {number} instanceId block instance id
 * @param {string} nonce channel-binding nonce copied into the parent iframe
 */
export const init = (instanceId, nonce) => {
    instanceId = Number(instanceId);
    if (window.parent === window || !Number.isInteger(instanceId) || instanceId <= 0 ||
            typeof nonce !== 'string' || !/^[a-f0-9]{32}$/.test(nonce)) {
        return;
    }

    let previousHeight = 0;
    const report = () => {
        const height = Math.max(200, Math.min(4000, Math.ceil(document.documentElement.scrollHeight)));
        if (height === previousHeight) {
            return;
        }
        previousHeight = height;
        window.parent.postMessage({
            type: 'block_interactembed:resize',
            version: 1,
            instanceId: instanceId,
            nonce: nonce,
            height: height,
        }, window.location.origin);
    };

    if ('ResizeObserver' in window) {
        const observer = new ResizeObserver(report);
        observer.observe(document.documentElement);
    }
    window.addEventListener('load', report, {once: true});
    report();
};
