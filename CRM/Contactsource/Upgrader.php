<?php
/*-------------------------------------------------------+
| SYSTOPIA Contact Source Extension                      |
| Copyright (C) 2019 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/


use CRM_Contactsource_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Contactsource_Upgrader extends CRM_Contactsource_Upgrader_Base {

  /**
   * Installer
   */
  public function install() {
    // just make sure the enable script runs
    $this->enable();
  }

  /**
   * Extension is enabled
   */
  public function enable() {
    // just make sure our activity type is there
    CRM_Contactsource_Configuration::getActivityTypeID();
  }

}
