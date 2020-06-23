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


/**
 * Collection of upgrade steps.
 */
class CRM_Contactsource_Configuration
{

    const CONTACT_SOURCE_ACTIVITY_TYPE = 'contact_source';
    const CONTACT_SOURCE_FIELD_LENGTH  = 255;

    protected static $activity_type_id = NULL;

    /**
     * Get the extensions "First Contact" activity type ID
     */
    public static function getActivityTypeID()
    {
        if (self::$activity_type_id === NULL) {
            $activity_types = civicrm_api3('OptionValue', 'get', [
                'option_group_id' => 'activity_type',
                'name' => self::CONTACT_SOURCE_ACTIVITY_TYPE
            ]);
            switch ($activity_types['count']) {
                case 0:
                    // doesn't exit yet => create
                    civicrm_api3('OptionValue', 'create', [
                        'option_group_id' => 'activity_type',
                        'name' => self::CONTACT_SOURCE_ACTIVITY_TYPE,
                        'label' => E::ts("First Contact"),
                        'is_active' => 1,
                        'is_reserved' => 1,
                        'filter' => 0,
                        'icon' => 'fa-user-plus']);
                    return self::getActivityTypeID();

                case 1:
                    // found one
                    $activity_type = reset($activity_types['values']);
                    if (empty($activity_type['is_active'])) {
                        // still needs to be activated
                        civicrm_api3('OptionValue', 'create', [
                            'id' => $activity_type['id'],
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

    /**
     * Get the default contact source activity subject.
     *
     * @return string
     *  '':               don't fill at all
     *  'campaign_title': fill with campaign title
     *
     */
    public static function getDefaultActivitySubject()
    {
        $value = Civi::settings()->get('contact_source_subject');
        if (empty($value)) {
            return '';
        } else {
            return $value;
        }
    }

    /**
     * Get the mode to sync the contact source activities to the
     *  contact's source field
     *
     * @return string
     * ''               => "disabled"
     * 'first_campaign' => "First contact (campaign)"
     * 'first_subject'  => "First contact (subject)"
     * 'all_campaign'   => "All contacts (campaign)"
     * 'all_subject'    => "All contacts (subject)"
     *
     */
    public static function getContactSourceSyncMode()
    {
        $value = Civi::settings()->get('contact_source_sync');
        if (empty($value)) {
            return '';
        } else {
            return $value;
        }
    }

    /**
     * Get the mode to sync the contact source activities to the
     *  contact's source field
     *
     * @return string
     * ''               => "disabled"
     * 'first_campaign' => "First contact (campaign)"
     * 'first_subject'  => "First contact (subject)"
     * 'all_campaign'   => "All contacts (campaign)"
     * 'all_subject'    => "All contacts (subject)"
     *
     */
    public static function sourceInjectionEnabled()
    {
        return !empty(Civi::settings()->get('contact_source_inject'));
    }
}
