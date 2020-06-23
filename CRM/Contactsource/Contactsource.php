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

    /**
     * Update contact source fields according to configured mode
     *
     * @param array $contact_ids
     *      contact IDs to update. If empty, ALL contacts are updated
     *
     * @return integer
     *   count of updated contacts
     */
    public static function updateContactSourceField($contact_ids = null)
    {
        $change_count = 0;
        $mode = CRM_Contactsource_Configuration::getContactSourceSyncMode();
        if (empty($mode)) {
            return 0;
        }

        // get activity type ID
        $activity_type_id = CRM_Contactsource_Configuration::getActivityTypeID();

        // get contact clause
        $CONTACT_CLAUSE = 'TRUE';
        if (!empty($contact_ids) && is_array($contact_ids)) {
            $clean_contact_ids = [];
            foreach ($contact_ids as $contact_id) {
                $clean_contact_ids[] = (int) $contact_id;
            }
            $CONTACT_CLAUSE = 'IN (' . implode(',', $clean_contact_ids) . ')';
        }

        if ($mode == 'first_campaign' || $mode == 'first_subject') {
        // SINGLE VALUE MODE
            // first step: find out which is the first contribution
            $first_contact_table = CRM_Utils_SQL_TempTable::build();
            $first_contact_table->createWithQuery("
                SELECT
                  contact.id                            AS contact_id,
                  contact.source                        AS current_source,
                  contact.source                        AS new_source,
                  MIN(first_contact.activity_date_time) AS first_contact
                FROM civicrm_contact contact
                LEFT JOIN civicrm_activity_contact ac 
                  ON contact.id = ac.contact_id
                  AND ac.record_type_id = 3
                LEFT JOIN civicrm_activity first_contact
                  ON first_contact.id = ac.activity_id
                  AND first_contact.activity_type_id = {$activity_type_id}
                WHERE {$CONTACT_CLAUSE}
                  AND first_contact.id IS NOT NULL
                GROUP BY contact.id;
            ");
            $first_contact_table_name = $first_contact_table->getName();
            CRM_Core_DAO::executeQuery("ALTER TABLE {$first_contact_table_name} ADD INDEX contact_id(contact_id)");

            // next step: calculate new subject
            $subject_term = ($mode == 'first_campaign') ? "campaign.title" : "activity.subject";
            CRM_Core_DAO::executeQuery("
            UPDATE {$first_contact_table_name} first_contact
            LEFT JOIN civicrm_activity_contact ac 
              ON ac.contact_id = first_contact.contact_id
              AND ac.record_type_id = 3
            LEFT JOIN civicrm_activity activity
              ON activity.id = ac.activity_id
              AND activity.activity_type_id = {$activity_type_id}
              AND activity.activity_date_time = first_contact.first_contact
            LEFT JOIN civicrm_campaign campaign
              ON campaign.id = activity.campaign_id
            SET first_contact.new_source = COALESCE({$subject_term})
            ");

            // calculate count
            $change_count = CRM_Core_DAO::singleValueQuery("
            SELECT COUNT(*) 
            FROM {$first_contact_table_name}
            WHERE (current_source IS NULL AND new_source IS NOT NULL)
               OR current_source <> new_source            
            ");

            // finally: update contact
            CRM_Core_DAO::executeQuery("
            UPDATE civicrm_contact contact
            LEFT JOIN {$first_contact_table_name} first_contact
              ON first_contact.contact_id = contact.id
            SET contact.source = first_contact.new_source
            WHERE (contact.source IS NULL AND first_contact.new_source IS NOT NULL)
               OR contact.source <> first_contact.new_source
            ");

            // cleanup
            $first_contact_table->drop();


        } elseif ($mode == 'all_campaign' || $mode == 'all_subject') {
        // AGGREGATED VALUE MODE
            // first step: find out which is the first contribution
            $first_contact_table = CRM_Utils_SQL_TempTable::build();
            $first_contact_table->createWithQuery("
                SELECT
                  contact.id                            AS contact_id,
                  first_contact.id                      AS activity_id
                FROM civicrm_contact contact
                LEFT JOIN civicrm_activity_contact ac 
                  ON contact.id = ac.contact_id
                  AND ac.record_type_id = 3
                LEFT JOIN civicrm_activity first_contact
                  ON first_contact.id = ac.activity_id
                  AND first_contact.activity_type_id = {$activity_type_id}
                WHERE {$CONTACT_CLAUSE}
                  AND first_contact.id IS NOT NULL
                ORDER BY first_contact.activity_date_time ASC
            ");
            $first_contact_table_name = $first_contact_table->getName();
            CRM_Core_DAO::executeQuery("ALTER TABLE {$first_contact_table_name} ADD INDEX contact_id(contact_id)");

            // seconds step: calculate old and new subject
            $subject_term = ($mode == 'all_campaign') ? "campaign.title" : "activity.subject";
            $subject_update_table = CRM_Utils_SQL_TempTable::build();
            $subject_update_table->createWithQuery("
                SELECT
                  first_contact.contact_id                                                  AS contact_id,
                  contact.source                                                            AS current_source,
                  SUBSTRING(GROUP_CONCAT(DISTINCT({$subject_term}) SEPARATOR ', '), 1, 255) AS new_source
                FROM {$first_contact_table_name} first_contact
                LEFT JOIN civicrm_activity activity
                  ON activity.id = first_contact.activity_id
                LEFT JOIN civicrm_campaign campaign
                  ON campaign.id = activity.campaign_id
                LEFT JOIN civicrm_contact contact
                  ON contact.id = first_contact.contact_id
                GROUP BY contact.id
            ");

            // calculate count
            $subject_update_table_name = $subject_update_table->getName();
            $change_count = CRM_Core_DAO::singleValueQuery("
            SELECT COUNT(*) 
            FROM {$subject_update_table_name}
            WHERE (current_source IS NULL AND new_source IS NOT NULL)
               OR current_source <> new_source            
            ");


            // finally: update contact
            CRM_Core_DAO::executeQuery("
            UPDATE civicrm_contact contact
            LEFT JOIN {$subject_update_table_name} first_contact
              ON first_contact.contact_id = contact.id
            SET contact.source = first_contact.new_source
            WHERE (contact.source IS NULL AND first_contact.new_source IS NOT NULL)
               OR contact.source <> first_contact.new_source
            ");

            // cleanup
            $first_contact_table->drop();
            $subject_update_table->drop();
        }

        return $change_count;
    }
}
