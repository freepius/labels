import NavigationSyncController from './js/controllers/navigation-sync-controller.js';
import DynamicTokenController from './js/controllers/dynamic-token-controller.js';
import ToolboxController from './js/controllers/toolbox-controller.js';
import VersionController from './js/controllers/version-controller.js';
import UrlStateService from './js/services/url-state-service.js';

/**
 * Application bootstrap.
 *
 * Keep this file intentionally small: all behavior lives in dedicated classes.
 */
document.addEventListener('DOMContentLoaded', () => {
    const urlState = new UrlStateService();
    const navigationSyncController = new NavigationSyncController(urlState);
    const versionController = new VersionController(urlState);
    const toolboxController = new ToolboxController();
    const dynamicTokenController = new DynamicTokenController(urlState, navigationSyncController);

    navigationSyncController.init();
    versionController.init();
    toolboxController.init();
    dynamicTokenController.init();
});
