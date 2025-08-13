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

    /**
     * Manage the toolbox on label page.
     */
    function manageToolbox() {
        const rotateLeftButton = document.getElementById('turn-left');
        const rotateRightButton = document.getElementById('turn-right');
        const resetButton = document.getElementById('reset');

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

    document.querySelectorAll('input[name="version"]').forEach(input => {
        input.addEventListener('change', updateVersionQuery);
    });

    manageToolbox();
});
