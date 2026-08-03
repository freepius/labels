/**
 * Build dynamic token inputs from label placeholders and keep content synchronized.
 *
 * Supported syntaxes:
 * - [[KEY]]
 * - [[KEY : default value]]
 */
export default class DynamicTokenController {
    constructor(urlState, navigationSyncController, {
        panelId = 'dynamic-fields',
        resetButtonId = 'dynamic-fields-reset',
        svgNamespace = 'http://www.w3.org/2000/svg',
    } = {}) {
        this.urlState = urlState;
        this.navigationSyncController = navigationSyncController;
        this.panelId = panelId;
        this.resetButtonId = resetButtonId;
        this.svgNamespace = svgNamespace;

        // Keep global syntax flexible while preserving NBSP inside default values.
        this.placeholderPattern = /\[\[\s*([\w-]+)\s*(?::[ \t\r\n]*(.*?)[ \t\r\n]*)?\]\]/g;

        this.tokensByKey = new Map();
        this.defaultValues = new Map();
    }

    init() {
        const textNodes = this.collectTextNodesWithPlaceholders();
        if (textNodes.length === 0) {
            this.navigationSyncController.syncFromCurrentState();
            return;
        }

        textNodes.forEach(textNode => this.replacePlaceholdersInNode(textNode));

        if (this.defaultValues.size === 0) {
            this.navigationSyncController.syncFromCurrentState();
            return;
        }

        const panel = this.getOrCreatePanel();
        const list = panel.querySelector('ul');

        if (!list) {
            return;
        }

        this.bindDynamicInputs(list);
        this.bindResetButton(panel);

        this.navigationSyncController.syncFromCurrentState();
    }

    collectTextNodesWithPlaceholders() {
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

            if (this.placeholderPattern.test(node.nodeValue)) {
                textNodes.push(node);
            }
            this.placeholderPattern.lastIndex = 0;
        }

        return textNodes;
    }

    replacePlaceholdersInNode(textNode) {
        const parent = textNode.parentElement;
        const text = textNode.nodeValue;
        const fragment = document.createDocumentFragment();
        let cursor = 0;

        text.replace(this.placeholderPattern, (fullMatch, key, defaultValue = '', offset) => {
            if (offset > cursor) {
                fragment.appendChild(document.createTextNode(text.slice(cursor, offset)));
            }

            const token = this.createTokenElement(parent, key, defaultValue);

            fragment.appendChild(token);
            this.addTokenForKey(key, token);
            this.registerDefaultValue(key, defaultValue);

            cursor = offset + fullMatch.length;
            return fullMatch;
        });

        if (cursor < text.length) {
            fragment.appendChild(document.createTextNode(text.slice(cursor)));
        }

        textNode.parentNode.replaceChild(fragment, textNode);
    }

    createTokenElement(parent, key, defaultValue) {
        const isSvg = this.isSvgContext(parent);
        const token = isSvg
            ? document.createElementNS(this.svgNamespace, 'tspan')
            : document.createElement('span');

        token.classList.add('dynamic-token');
        token.setAttribute('data-dynamic-key', key);
        token.textContent = defaultValue;

        return token;
    }

    isSvgContext(element) {
        if (element.namespaceURI === this.svgNamespace) {
            return true;
        }

        return element.closest('svg') !== null && element.closest('foreignObject') === null;
    }

    addTokenForKey(key, token) {
        if (!this.tokensByKey.has(key)) {
            this.tokensByKey.set(key, []);
        }

        this.tokensByKey.get(key).push(token);
    }

    registerDefaultValue(key, defaultValue) {
        const knownDefault = this.defaultValues.get(key);

        if (knownDefault === undefined || (knownDefault === '' && defaultValue !== '')) {
            this.defaultValues.set(key, defaultValue);
        }
    }

    getOrCreatePanel() {
        let panel = document.getElementById(this.panelId);

        if (!panel) {
            panel = document.createElement('nav');
            panel.id = this.panelId;
            panel.innerHTML = '<h1>Valeurs dynamiques</h1><ul></ul>';
            document.body.appendChild(panel);
        }

        return panel;
    }

    bindDynamicInputs(list) {
        const queryValues = this.urlState.readDynamicValues();

        this.defaultValues.forEach((defaultValue, key) => {
            const input = this.getOrCreateInputForKey(list, key, defaultValue);
            const effectiveValue = queryValues.get(key) ?? defaultValue;

            input.value = effectiveValue;
            this.applyValue(key, effectiveValue);

            input.addEventListener('input', event => {
                const value = event.target.value;

                this.applyValue(key, value);
                this.urlState.setDynamicValue(key, value, defaultValue);
                this.navigationSyncController.syncFromCurrentState();
            });
        });
    }

    getOrCreateInputForKey(list, key, defaultValue) {
        let input = list.querySelector(`[data-dynamic-input="${CSS.escape(key)}"]`);

        if (input) {
            return input;
        }

        const item = document.createElement('li');
        const safeId = `dynamic-field-${key.replace(/[^a-zA-Z0-9_-]/g, '-').toLowerCase()}`;

        item.innerHTML = `
            <label for="${safeId}">${key}</label>
            <input id="${safeId}" type="text" data-dynamic-input="${key}" value="${defaultValue}" placeholder="${key}">
        `;

        list.appendChild(item);
        input = item.querySelector('input');

        return input;
    }

    bindResetButton(panel) {
        let resetButton = panel.querySelector(`#${this.resetButtonId}`);

        if (!resetButton) {
            resetButton = document.createElement('button');
            resetButton.id = this.resetButtonId;
            resetButton.type = 'button';
            resetButton.textContent = 'Reinitialiser';
            panel.appendChild(resetButton);
        }

        resetButton.addEventListener('click', () => {
            this.defaultValues.forEach((defaultValue, key) => {
                const input = panel.querySelector(`[data-dynamic-input="${CSS.escape(key)}"]`);

                if (!input) {
                    return;
                }

                input.value = defaultValue;
                this.applyValue(key, defaultValue);
                this.urlState.setDynamicValue(key, defaultValue, defaultValue);
            });

            this.navigationSyncController.syncFromCurrentState();
        });
    }

    applyValue(key, value) {
        const tokens = this.tokensByKey.get(key) ?? [];

        tokens.forEach(token => {
            token.textContent = value;
        });
    }
}