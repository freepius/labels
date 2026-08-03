/**
 * Manage label rotation controls in the toolbox.
 */
export default class ToolboxController {
    constructor({labelSelector = '.label', rotationStep = 90} = {}) {
        this.labelSelector = labelSelector;
        this.rotationStep = rotationStep;
        this.availableRotations = Array.from({length: 360 / this.rotationStep}, (_, i) => i * this.rotationStep);
    }

    init() {
        const rotateLeftButton = document.getElementById('turn-left');
        const rotateRightButton = document.getElementById('turn-right');
        const resetButton = document.getElementById('reset');

        if (!rotateLeftButton || !rotateRightButton || !resetButton) {
            return;
        }

        rotateLeftButton.addEventListener('click', () => this.rotate(-1));
        rotateRightButton.addEventListener('click', () => this.rotate(1));
        resetButton.addEventListener('click', () => this.removeRotationClasses());
    }

    getLabelElement() {
        return document.querySelector(this.labelSelector);
    }

    removeRotationClasses() {
        const label = this.getLabelElement();

        if (!label) {
            return;
        }

        this.availableRotations.forEach(deg => label.classList.remove(`rotate-${deg}`));
    }

    rotate(factor) {
        const label = this.getLabelElement();

        if (!label) {
            return;
        }

        const hasRotationClass = deg => label.classList.contains(`rotate-${deg}`);
        const currentRotation = this.availableRotations.find(deg => hasRotationClass(deg)) || 0;
        const newRotation = (currentRotation + factor * this.rotationStep + 360) % 360;

        this.removeRotationClasses();
        label.classList.add(`rotate-${newRotation}`);
    }
}