/**
 * Handle the versions panel and keep the "v" query parameter in sync.
 */
export default class VersionController {
    constructor(urlState, {selector = 'input[name="version"]'} = {}) {
        this.urlState = urlState;
        this.selector = selector;
    }

    init() {
        document.querySelectorAll(this.selector).forEach(input => {
            input.addEventListener('change', () => this.updateVersionQuery());
        });
    }

    updateVersionQuery() {
        const checkedVersions = Array.from(document.querySelectorAll(`${this.selector}:checked`))
            .map(input => input.value)
            .join(',');

        this.urlState.navigateWithVersion(checkedVersions);
    }
}