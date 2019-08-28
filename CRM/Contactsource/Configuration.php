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
class CRM_Contactsource_Configuration {

  const CONTACT_SOURCE_ACTIVITY_TYPE = 'contact_source';

  protected static $activity_type_id = NULL;

  /**
   * Get the extensions "First Contact" activity type ID
   */
  public static function getActivityTypeID() {
    if (self::$activity_type_id === NULL) {
      $activity_types = civicrm_api3('OptionValue', 'get', [
          'option_group_id' => 'activity_type',
          'name'            => self::CONTACT_SOURCE_ACTIVITY_TYPE
      ]);
      switch ($activity_types['count']) {
        case 0:
          // doesn't exit yet => create
          civicrm_api3('OptionValue', 'create', [
              'option_group_id' => 'activity_type',
              'name'            => self::CONTACT_SOURCE_ACTIVITY_TYPE,
              'label'           => E::ts("First Contact"),
              'is_active'       => 1,
              'is_reserved'     => 1,
              'filter'          => 0,
              'icon'            => 'fa-user-plus']);
          return self::getActivityTypeID();

        case 1:
          // found one
          $activity_type = reset($activity_types['values']);
          if (empty($activity_type['is_active'])) {
            // still needs to be activated
            civicrm_api3('OptionValue', 'create', [
                'id'        => $activity_type['id'],
                'is_active' => 1]);
          }
          self::$activity_type_id = $activity_type['value'];
          break;

        default:
          // found multiple ones
          throw new Exception("Multiple activity types of name '" . self::CONTACT_SOURCE_ACTIVITY_TYPE . "' found. Please fix activity_type option group!");
      }
    }
    return self::$activity_type_id;
  }
}
