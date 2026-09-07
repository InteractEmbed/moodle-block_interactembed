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
 * Interactive controls for one InteractEmbed player.
 *
 * @module     block_interactembed/player
 * @copyright  2026 Michel Cardinal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';

/**
 * Initialises one isolated InteractEmbed player.
 *
 * @param {string} rootId unique player element id
 */
export const init = rootId => {
    const root = document.getElementById(rootId);
    if (!root || root.dataset.initialised === 'true') {
        return;
    }
    root.dataset.initialised = 'true';

    const iframe = root.querySelector('.block-interactembed-iframe');
    const loadButton = root.querySelector('[data-action="load-content"]');
    if (loadButton) {
        loadButton.addEventListener('click', () => {
            const frame = root.querySelector('.block-interactembed-frame');
            const launch = root.querySelector('.block-interactembed-launch');
            const actions = root.querySelector('.block-interactembed-actions');
            if (iframe.dataset.src) {
                iframe.src = iframe.dataset.src;
                delete iframe.dataset.src;
            }
            launch.hidden = true;
            frame.hidden = false;
            actions.hidden = false;
            iframe.addEventListener('load', () => iframe.focus(), {once: true});
        });
    }
    const fullscreen = root.querySelector('[data-action="fullscreen"]');
    if (fullscreen) {
        let nativeFullscreenActive = false;
        const label = fullscreen.querySelector('[data-role="fullscreen-label"]');
        const fallbackClass = 'block-interactembed-player--fullscreen-fallback';
        const bodyClass = 'block-interactembed-body-locked';
        const focusableSelector = 'a[href], button:not([disabled]), textarea:not([disabled]), ' +
            'input:not([disabled]), select:not([disabled]), iframe, [tabindex]:not([tabindex="-1"])';
        const fallbackFocusables = () => Array.from(root.querySelectorAll(focusableSelector)).filter(element =>
            !element.closest('[hidden]') && element.getAttribute('aria-hidden') !== 'true'
        );
        const updateFullscreenControl = active => {
            fullscreen.setAttribute('aria-pressed', active ? 'true' : 'false');
            const stringId = active ? 'exitfullscreen' : 'fullscreen';
            return getString(stringId, 'block_interactembed').then(value => {
                label.textContent = value;
                return value;
            });
        };
        const exitFallback = () => {
            if (!root.classList.contains(fallbackClass)) {
                return;
            }
            root.classList.remove(fallbackClass);
            document.body.classList.remove(bodyClass);
            root.removeAttribute('role');
            root.removeAttribute('aria-modal');
            root.removeAttribute('aria-label');
            updateFullscreenControl(false);
            fullscreen.focus();
        };
        const enterFallback = () => {
            root.classList.add(fallbackClass);
            document.body.classList.add(bodyClass);
            root.setAttribute('role', 'dialog');
            root.setAttribute('aria-modal', 'true');
            root.setAttribute('aria-label', root.dataset.fullscreenLabel);
            updateFullscreenControl(true);
        };
        const trapFallbackFocus = event => {
            if (event.key !== 'Tab' || !root.classList.contains(fallbackClass)) {
                return;
            }
            const focusables = fallbackFocusables();
            if (!focusables.length) {
                event.preventDefault();
                fullscreen.focus();
                return;
            }
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            const outside = !root.contains(document.activeElement);
            if (event.shiftKey && (document.activeElement === first || outside)) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && (document.activeElement === last || outside)) {
                event.preventDefault();
                first.focus();
            }
        };

        fullscreen.addEventListener('click', async() => {
            if (root.classList.contains(fallbackClass)) {
                exitFallback();
                return;
            }
            if (document.fullscreenElement === root) {
                await document.exitFullscreen();
                return;
            }
            if (!root.requestFullscreen) {
                enterFallback();
                return;
            }
            try {
                await root.requestFullscreen();
            } catch (error) {
                enterFallback();
            }
        });
        document.addEventListener('fullscreenchange', () => {
            const active = document.fullscreenElement === root;
            if (active) {
                nativeFullscreenActive = true;
                updateFullscreenControl(true);
            } else if (nativeFullscreenActive) {
                nativeFullscreenActive = false;
                updateFullscreenControl(false);
                fullscreen.focus();
            }
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                exitFallback();
                return;
            }
            trapFallbackFocus(event);
        });
    }

    const copyButton = root.querySelector('[data-action="copy-embed"]');
    if (copyButton) {
        copyButton.addEventListener('click', async() => {
            const textarea = root.querySelector('textarea');
            const status = root.querySelector('.block-interactembed-copy-status');
            try {
                await navigator.clipboard.writeText(textarea.value);
                status.textContent = await getString('embedcodecopied', 'block_interactembed');
            } catch (error) {
                textarea.focus();
                textarea.select();
                document.execCommand('copy');
                status.textContent = await getString('embedcodecopied', 'block_interactembed');
            }
        });
    }
};
