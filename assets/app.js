document.addEventListener('DOMContentLoaded', function () {
    const queryKeyPrefix = 'dyn.';

    /**
     * Update the version (v) query parameter in the URL based on checked versions.
     * Then reload the page.
     * If no versions are checked, the "v" parameter is removed.
     */
    function updateVersionQuery() {
        const checkedVersions = Array.from(document.querySelectorAll('input[name="version"]:checked'))
            .map(input => input.value)
            .join(',');

        const url = new URL(window.location.href);

        if (checkedVersions) {
            url.searchParams.set('v', checkedVersions);
        } else {
            url.searchParams.delete('v');
        }

        window.location.href = url.toString();
    }

    /**
     * Keep static navigation links in sync with current dynamic query params.
     */
    function syncDynamicQueryInNavigationLinks() {
        const url = new URL(window.location.href);
        const dynamicEntries = [];

        url.searchParams.forEach((value, key) => {
            if (key.startsWith(queryKeyPrefix)) {
                dynamicEntries.push([key, value]);
            }
        });

        document.querySelectorAll('#pages a[href]').forEach(link => {
            const nextUrl = new URL(link.getAttribute('href'), window.location.origin);

            const keysToDelete = [];
            nextUrl.searchParams.forEach((_, key) => {
                if (key.startsWith(queryKeyPrefix)) {
                    keysToDelete.push(key);
                }
            });
            keysToDelete.forEach(key => nextUrl.searchParams.delete(key));

            dynamicEntries.forEach(([key, value]) => {
                nextUrl.searchParams.set(key, value);
            });

            link.setAttribute('href', `${nextUrl.pathname}${nextUrl.search}${nextUrl.hash}`);
        });
    }

    /**
     * Manage the toolbox on label page.
     */
    function manageToolbox() {
        const rotateLeftButton = document.getElementById('turn-left');
        const rotateRightButton = document.getElementById('turn-right');
        const resetButton = document.getElementById('reset');

        if (!rotateLeftButton || !rotateRightButton || !resetButton) {
            return;
        }

        const rotationStep = 90;
        const availableRotations = Array.from({length: 360 / rotationStep}, (_, i) => i * rotationStep);

        function removeRotationClasses() {
            const label = document.querySelector('.label');
            availableRotations.forEach(deg => label.classList.remove(`rotate-${deg}`));
        }

        // Set the new rotation class.
        function rotate(factor) {
            const label = document.querySelector('.label');
            const has = deg => label.classList.contains(`rotate-${deg}`);
            const current = availableRotations.find(deg => has(deg)) || 0;
            const newRotation = (current + factor * rotationStep + 360) % 360;

            removeRotationClasses();
            label.classList.add(`rotate-${newRotation}`);
        }

        rotateLeftButton.addEventListener('click', () => rotate(-1));
        rotateRightButton.addEventListener('click', () => rotate(1));
        resetButton.addEventListener('click', removeRotationClasses);
    }

    /**
     * Build dynamic field inputs from placeholders and keep label content synchronized.
     *
     * Supported syntaxes in label text nodes:
     * - [[KEY]]
     * - [[KEY : default value]]
     *
     * Implementation notes:
     * - We parse TEXT nodes only, so placeholders can live in mixed HTML/SVG content.
    * - Default values must stay plain text. If styling/positioning is needed
    *   (`<b>`, `<tspan>`, etc.), the markup must wrap `[[KEY]]` instead of
    *   being embedded inside the placeholder itself.
     * - Replacement nodes are namespace-aware (`span` in HTML, `tspan` in SVG).
     * - Current values are mirrored to URL query params (`dyn.KEY=value`) for sharing/reload.
     */
    function manageDynamicTokens() {
        // KEY is constrained to letters/digits/_/- to keep query parameters predictable.
        // Keep the global syntax flexible, but preserve NBSP specifically inside default values.
        const placeholderPattern = /\[\[\s*([\w-]+)\s*(?::[ \t\r\n]*(.*?)[ \t\r\n]*)?\]\]/g;
        const svgNamespace = 'http://www.w3.org/2000/svg';
        const tokens = [];
        const initialValues = new Map();

        /**
         * Detect whether a text node lives in SVG text content.
         *
         * The direct parent is not necessarily the root <svg> element: it can be
         * <text>, <textPath>, <tspan>, etc. We therefore accept either:
         * - an element already in the SVG namespace, or
         * - any element under an <svg> ancestor, unless it is inside <foreignObject>
         *   where regular HTML nodes must stay HTML.
         */
        const isSvgContext = element => {
            if (element.namespaceURI === svgNamespace) {
                return true;
            }

            return element.closest('svg') !== null && element.closest('foreignObject') === null;
        };

        // Restore previously edited dynamic values from the URL.
        const readValuesFromQuery = () => {
            const url = new URL(window.location.href);
            const values = new Map();

            url.searchParams.forEach((value, key) => {
                if (!key.startsWith(queryKeyPrefix)) {
                    return;
                }

                values.set(key.slice(queryKeyPrefix.length), value);
            });

            return values;
        };

        // Persist only non-default values to avoid noisy URLs.
        const updateQueryValue = (key, value, defaultValue) => {
            const url = new URL(window.location.href);
            const queryKey = `${queryKeyPrefix}${key}`;

            if (value === defaultValue) {
                url.searchParams.delete(queryKey);
            } else {
                url.searchParams.set(queryKey, value);
            }

            window.history.replaceState(null, '', url);
        };

        // Collect all text nodes that contain at least one placeholder.
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        const textNodes = [];

        while (walker.nextNode()) {
            const node = walker.currentNode;
            if (!node.parentElement) {
                continue;
            }

            const parentTagName = node.parentElement.tagName;
            if (parentTagName === 'SCRIPT' || parentTagName === 'STYLE') {
                continue;
            }

            if (placeholderPattern.test(node.nodeValue)) {
                textNodes.push(node);
            }
            placeholderPattern.lastIndex = 0;
        }

        if (textNodes.length === 0) {
            return;
        }

        // Replace each placeholder by a concrete token node and keep surrounding text unchanged.
        textNodes.forEach(textNode => {
            const parent = textNode.parentElement;
            const text = textNode.nodeValue;
            const fragment = document.createDocumentFragment();
            let cursor = 0;

            text.replace(placeholderPattern, (fullMatch, key, defaultValue = '', offset) => {
                if (offset > cursor) {
                    fragment.appendChild(document.createTextNode(text.slice(cursor, offset)));
                }

                const isSvg = isSvgContext(parent);
                const token = isSvg
                    ? document.createElementNS(svgNamespace, 'tspan')
                    : document.createElement('span');

                token.classList.add('dynamic-token');
                token.setAttribute('data-dynamic-key', key);
                token.textContent = defaultValue;
                fragment.appendChild(token);
                tokens.push(token);

                const knownDefault = initialValues.get(key);
                if (
                    knownDefault === undefined
                    || (knownDefault === '' && defaultValue !== '')
                ) {
                    // If KEY appears multiple times, keep the first non-empty default we encounter.
                    initialValues.set(key, defaultValue);
                }

                cursor = offset + fullMatch.length;
                return fullMatch;
            });

            if (cursor < text.length) {
                fragment.appendChild(document.createTextNode(text.slice(cursor)));
            }

            textNode.parentNode.replaceChild(fragment, textNode);
        });

        // Build a panel with input fields for each dynamic KEY and keep it in sync with the label content.
        let panel = document.getElementById('dynamic-fields');
        if (!panel) {
            panel = document.createElement('nav');
            panel.id = 'dynamic-fields';
            panel.innerHTML = '<h1>Valeurs dynamiques</h1><ul></ul>';
            document.body.appendChild(panel);
        }

        const list = panel.querySelector('ul');
        if (!list) {
            return;
        }

        // Update all token occurrences sharing the same KEY.
        const applyValue = (key, value) => {
            tokens
                .filter(token => token.dataset.dynamicKey === key)
                .forEach(token => {
                    token.textContent = value;
                });
        };

        const queryValues = readValuesFromQuery();

        // For each KEY, create an input field and keep it in sync with the label content and URL query state.
        initialValues.forEach((defaultValue, key) => {
            let input = panel.querySelector(`[data-dynamic-input="${CSS.escape(key)}"]`);

            if (!input) {
                const item = document.createElement('li');
                const safeId = `dynamic-field-${key.replace(/[^a-zA-Z0-9_-]/g, '-').toLowerCase()}`;

                item.innerHTML = `
                    <label for="${safeId}">${key}</label>
                    <input id="${safeId}" type="text" data-dynamic-input="${key}" value="${defaultValue}" placeholder="${key}">
                `;

                list.appendChild(item);
                input = item.querySelector('input');
            }

            const effectiveValue = queryValues.get(key) ?? defaultValue;
            input.value = effectiveValue;

            applyValue(key, input.value);
            input.addEventListener('input', event => {
                const value = event.target.value;

                applyValue(key, value);
                updateQueryValue(key, value, defaultValue);
                syncDynamicQueryInNavigationLinks();
            });
        });

        // Add a reset button to restore all dynamic values to their defaults.
        let resetButton = panel.querySelector('#dynamic-fields-reset');
        if (!resetButton) {
            resetButton = document.createElement('button');
            resetButton.id = 'dynamic-fields-reset';
            resetButton.type = 'button';
            resetButton.textContent = 'Reinitialiser';
            panel.appendChild(resetButton);
        }

        // Reset all keys to defaults and clean URL query state.
        resetButton.addEventListener('click', () => {
            initialValues.forEach((defaultValue, key) => {
                const input = panel.querySelector(`[data-dynamic-input="${CSS.escape(key)}"]`);

                if (!input) {
                    return;
                }

                input.value = defaultValue;
                applyValue(key, defaultValue);
                updateQueryValue(key, defaultValue, defaultValue);
            });

            syncDynamicQueryInNavigationLinks();
        });

        syncDynamicQueryInNavigationLinks();
    }

    document.querySelectorAll('input[name="version"]').forEach(input => {
        input.addEventListener('change', updateVersionQuery);
    });

    manageToolbox();
    manageDynamicTokens();
});
