document.addEventListener('DOMContentLoaded', function () {
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

    document.querySelectorAll('input[name="version"]').forEach(input => {
        input.addEventListener('change', updateVersionQuery);
    });
});
