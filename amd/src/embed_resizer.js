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
 * Same-origin parent-page resizer for copied InteractEmbed iframe code.
 *
 * @module     block_interactembed/embed_resizer
 * @copyright  2026 Michel Cardinal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialises resizing of same-site copied iframe snippets.
 */
export const init = () => {

    if (window.interactEmbedResizerInitialised) {
        return;
    }
    window.interactEmbedResizerInitialised = true;

    const noncePattern = /^[a-f0-9]{32}$/;
    const expectedFields = 'height,instanceId,nonce,type,version';

    const createNonce = function() {
        if (!window.crypto || typeof window.crypto.getRandomValues !== 'function') {
            return '';
        }
        const values = new Uint8Array(16);
        window.crypto.getRandomValues(values);
        let nonce = '';
        values.forEach(function(value) {
            nonce += value.toString(16).padStart(2, '0');
        });
        return nonce;
    };

    const prepareFrame = function(frame) {
        const instanceId = Number(frame.dataset.interactembedId);
        if (!Number.isInteger(instanceId) || instanceId <= 0) {
            return;
        }
        let url;
        try {
            url = new URL(frame.src, document.baseURI);
        } catch (error) {
            return;
        }
        if (url.origin !== window.location.origin || !/\/blocks\/interactembed\/embed\.php$/.test(url.pathname)) {
            return;
        }
        let nonce = frame.dataset.interactembedNonce;
        if (!noncePattern.test(nonce || '')) {
            nonce = createNonce();
            if (!nonce) {
                return;
            }
            frame.dataset.interactembedNonce = nonce;
        }
        if (url.searchParams.get('nonce') !== nonce) {
            url.searchParams.set('nonce', nonce);
            frame.src = url.toString();
        }
    };

    document.querySelectorAll('iframe[data-interactembed-id]').forEach(prepareFrame);

    window.addEventListener('message', function(event) {
        const data = event.data;
        if (event.origin !== window.location.origin || !event.source ||
                Object.prototype.toString.call(data) !== '[object Object]' ||
                Object.keys(data).sort().join(',') !== expectedFields ||
                data.type !== 'block_interactembed:resize' || data.version !== 1 ||
                typeof data.instanceId !== 'number' || !Number.isInteger(data.instanceId) || data.instanceId <= 0 ||
                typeof data.nonce !== 'string' || !noncePattern.test(data.nonce) ||
                typeof data.height !== 'number' || !Number.isInteger(data.height) ||
                data.height < 200 || data.height > 4000) {
            return;
        }

        const frames = document.querySelectorAll('iframe[data-interactembed-id="' + data.instanceId + '"]');
        frames.forEach(function(frame) {
            if (frame.contentWindow === event.source && frame.dataset.interactembedNonce === data.nonce) {
                frame.style.height = data.height + 'px';
                frame.setAttribute('height', String(data.height));
            }
        });
    });
};
