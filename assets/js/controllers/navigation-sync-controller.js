/**
 * Keep static navigation links synchronized with dynamic query values.
 *
 * This is needed because links rendered by Twig are static at page load time,
 * while dynamic token edits happen client-side.
 */
export default class NavigationSyncController {
    constructor(urlState, {selector = '#pages a[href]'} = {}) {
        this.urlState = urlState;
        this.selector = selector;
    }

    init() {
        this.syncFromCurrentState();
    }

    syncFromCurrentState() {
        const dynamicEntries = this.urlState.getDynamicEntries();

        document.querySelectorAll(this.selector).forEach(link => {
            const nextUrl = new URL(link.getAttribute('href'), window.location.origin);

            this.urlState.applyDynamicEntriesToUrl(nextUrl, dynamicEntries);
            link.setAttribute('href', `${nextUrl.pathname}${nextUrl.search}${nextUrl.hash}`);
        });
    }
}