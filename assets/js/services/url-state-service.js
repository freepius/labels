export default class UrlStateService {
    constructor({dynamicPrefix = 'dyn.', versionKey = 'v'} = {}) {
        this.dynamicPrefix = dynamicPrefix;
        this.versionKey = versionKey;
    }

    getCurrentUrl() {
        return new URL(window.location.href);
    }

    dynamicQueryKey(key) {
        return `${this.dynamicPrefix}${key}`;
    }

    readDynamicValues() {
        const values = new Map();
        const url = this.getCurrentUrl();

        url.searchParams.forEach((value, key) => {
            if (!key.startsWith(this.dynamicPrefix)) {
                return;
            }

            values.set(key.slice(this.dynamicPrefix.length), value);
        });

        return values;
    }

    getDynamicEntries() {
        const entries = [];
        const url = this.getCurrentUrl();

        url.searchParams.forEach((value, key) => {
            if (key.startsWith(this.dynamicPrefix)) {
                entries.push([key, value]);
            }
        });

        return entries;
    }

    applyDynamicEntriesToUrl(url, entries) {
        const keysToDelete = [];

        url.searchParams.forEach((_, key) => {
            if (key.startsWith(this.dynamicPrefix)) {
                keysToDelete.push(key);
            }
        });

        keysToDelete.forEach(key => url.searchParams.delete(key));
        entries.forEach(([key, value]) => url.searchParams.set(key, value));
    }

    setDynamicValue(key, value, defaultValue) {
        const url = this.getCurrentUrl();
        const queryKey = this.dynamicQueryKey(key);

        if (value === defaultValue) {
            url.searchParams.delete(queryKey);
        } else {
            url.searchParams.set(queryKey, value);
        }

        window.history.replaceState(null, '', url);
    }

    navigateWithVersion(checkedVersions) {
        const url = this.getCurrentUrl();

        if (checkedVersions) {
            url.searchParams.set(this.versionKey, checkedVersions);
        } else {
            url.searchParams.delete(this.versionKey);
        }

        window.location.href = url.toString();
    }
}