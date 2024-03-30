/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

import PersistentStorage from "@typo3/backend/storage/persistent.js";

class ContainerToggle {
  containerColumnToggle = '.t3js-toggle-container-column';

  constructor() {
    this.initializeContainerToggle();
  }

  /**
   * initialize the toggle icons to open listings of nested grid container structure in the list module
   */
  initializeContainerToggle() {
    document.querySelectorAll(this.containerColumnToggle).forEach(container=> {
      container.addEventListener('click', event => this.toggleClicked(event));
    });
  }

  toggleClicked(event) {
    event.preventDefault();

    let column = event.currentTarget,
      container = column.closest('td').dataset['colpos'],
      isExpanded = column.dataset['state'] === 'expanded';

    // Store collapse state in UC
    let storedModuleDataList = {};

    if (PersistentStorage.isset('moduleData.list.containerExpanded')) {
      storedModuleDataList = PersistentStorage.get('moduleData.list.containerExpanded');
    }

    let expandConfig = {};
    expandConfig[container] = isExpanded ? "1" : "0";

    storedModuleDataList = Object.assign(storedModuleDataList, expandConfig);

    PersistentStorage.set('moduleData.list.containerExpanded', storedModuleDataList).then(() => {
      column.dataset['state'] = isExpanded ? 'collapsed' : 'expanded';
      if (isExpanded) {
        column.closest('td').classList.add('collapsed');
      } else {
        column.closest('td').classList.remove('collapsed');
      }
    });
  }
}


export default new ContainerToggle;
