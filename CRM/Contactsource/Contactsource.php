<?php
/*-------------------------------------------------------+
| SYSTOPIA Contact Source Extension                      |
| Copyright (C) 2019-2020 SYSTOPIA                       |
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
 * Contact Source Functions
 */
class CRM_Contactsource_Contactsource
{

    /**
     * Inject contact source (by activity) into summary view
     *
     * @param $page CRM_Core_Page
     */
    public static function injectInPage($page)
    {
        $pageName = $page->getVar('_name');
        // only if contact summary
        if ($pageName == 'CRM_Contact_Page_View_Summary') {
            $contact_id = $page->getVar('_contactId');
            $contact_source_string = self::getContactSourceString($contact_id, 2);
            if ($contact_source_string) {
                $page->assign('contact_source_string', $contact_source_string);
                CRM_Core_Region::instance('page-body')->add(array(
                    'template' => 'CRM/Contactsource/Contactsource.tpl'));
            }
        }
    }

    /**
     * Get a string summing up the list of sources
     *
     * @param integer $contact_id
     *   contact ID
     * @param integer $max_len
     *    maximum number of sources
     * @return string
     *    calculated string
     */
    public static function getContactSourceString($contact_id, $max_len)
    {
        $activities = self::getContactSources($contact_id);
        $contact_sources = '';
        $sources_joined = 0;
        foreach ($activities as $activity) {
            if ($sources_joined >= $max_len) {
                break;
            }

            if (!empty($activity['subject'])) {
                if ($contact_sources) {
                    $contact_sources .= ', ';
                }
                $contact_sources .= $activity['subject'];
                $sources_joined += 1;
            }
        }

        if ($max_len < count($activities)) {
            $contact_sources .= ', ...';
        }
        return $contact_sources;
    }

    /**
     * Return a chronologically ordered list of the contact's sources
     *
     * @param integer $contact_id
     *  contact ID
     *
     * @return array
     *  list of activities
     */
    public static function getContactSources($contact_id)
    {
        if (empty($contact_id)) {
            return [];
        }

        // look up activities
        static $sources_by_contact = [];
        if (!isset($sources_by_contact[$contact_id])) {
            $sources_by_contact[$contact_id] = [];
            $activities = civicrm_api3('Activity', 'get', [
                'target_contact_id' => $contact_id,
                'activity_type_id' => CRM_Contactsource_Configuration::getActivityTypeID(),
                'option' => ['limit' => 0,
                    'sort' => 'activity_date_time asc'],
                'return' => 'datetime,subject,campaign',
            ]);
            foreach ($activities['values'] as $activity) {
                $sources_by_contact[$contact_id][] = $activity;
            }
        }
        return $sources_by_contact[$contact_id];
    }


    /**
     * Calculate the contact source activity subject based on the settings
     *
     * @return string
     *   suggested activity source
     */
    public static function getContactSourceActivitySubject($activity_data) {
        $fill_mode = CRM_Contactsource_Configuration::getDefaultActivitySubject();
        if ($fill_mode == 'campaign_title'
            && !empty($activity_data['campaign_id'])) {
            return civicrm_api3('Campaign', 'getvalue', [
                'id' => $activity_data['campaign_id'],
                'return' => 'title',
            ]);
        }

        // fallback: nothing
        return '';
    }

    /**
     * Will update (i.e. fill) all activity subjects according to the settings
     *
     * @return integer
     *   count of updated contacts
     */
    public static function updateAllContactSourceActivitySubjects()
    {
        $fill_mode = CRM_Contactsource_Configuration::getDefaultActivitySubject();
        if (empty($fill_mode)) {
            return 0;
        }

        $activity_type_id = CRM_Contactsource_Configuration::getActivityTypeID();
        if ($fill_mode == 'campaign_title') {
            // find out how many are empty
            $eligible_fields = CRM_Core_DAO::singleValueQuery("
                SELECT COUNT(*)
                FROM civicrm_activity first_contact_activity
                WHERE activity_type_id = {$activity_type_id}
                  AND (subject IS NULL OR subject = '')
                  AND campaign_id IS NOT NULL
            ");
            // fill the subject
            CRM_Core_DAO::executeQuery("
                UPDATE civicrm_activity    first_contact_activity
                LEFT JOIN civicrm_campaign first_contact_campaign ON first_contact_campaign.id = first_contact_activity.campaign_id
                SET first_contact_activity.subject = first_contact_campaign.title
                WHERE activity_type_id = {$activity_type_id}
                  AND (subject IS NULL OR subject = '')
                  AND campaign_id IS NOT NULL                
            ");
            return $eligible_fields;

        } else {
            throw new Exception("Subject fill mode '{$fill_mode}' undefined!");
        }
    }

}
