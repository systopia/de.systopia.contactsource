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


require_once 'contactsource.civix.php';
use CRM_Contactsource_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function contactsource_civicrm_config(&$config) {
  _contactsource_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function contactsource_civicrm_install() {
  _contactsource_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function contactsource_civicrm_enable() {
  _contactsource_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_buildForm().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function contactsource_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Contact') {
    if (CRM_Contactsource_ActivityCreation::shouldInject($form)) {
      CRM_Contactsource_ActivityCreation::buildForm($formName, $form);
    }
  }
}

/**
 * Implements hook_civicrm_postProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postProcess
 */
function contactsource_civicrm_postProcess($formName, &$form) {
  if ($formName == 'CRM_Contact_Form_Contact') {
    if (CRM_Contactsource_ActivityCreation::shouldInject($form)) {
      CRM_Contactsource_ActivityCreation::postProcess($formName, $form);
    }
  }
}

/**
 * Implements hook_civicrm_pageRun
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pageRun
 */
function contactsource_civicrm_pageRun(&$page) {
  CRM_Contactsource_Contactsource::injectInPage($page);
}

/**
 * Implements hook_civicrm_pageRun
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pageRun
 */
function contactsource_civicrm_pre($op, $objectName, $id, &$params) {
    if ($objectName == 'Activity' && $op == 'create') {
        $contact_source_activity_type = CRM_Contactsource_Configuration::getActivityTypeID();
        if ($params['activity_type_id'] == $contact_source_activity_type) {
            // this is a contact source activity type
            if (empty($params['subject'])) {
                $params['subject'] = CRM_Contactsource_Contactsource::getContactSourceActivitySubject($params);
            }
        }
    }
}

/**
 * Implements hook_civicrm_pageRun
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_pageRun
 */
function contactsource_civicrm_post($op, $objectName, $objectId, &$objectRef) {
    if ($objectName == 'Activity' && ($op == 'edit' || $op == 'create')) {
        $contact_source_activity_type = CRM_Contactsource_Configuration::getActivityTypeID();
        if (isset($objectRef->activity_type_id)) {
            $activity_type_id = $objectRef->activity_type_id;
        } else {
            $activity_type_id = civicrm_api3('Activity', 'getvalue', [
                'id' => $objectId,
                'return' => 'activity_type_id']);
        }
        if ($activity_type_id == $contact_source_activity_type) {
            // maybe we need to update the contact's source field:
            if (CRM_Contactsource_Configuration::getContactSourceSyncMode()) {
                // get contacts
                $contact_ids = CRM_Core_DAO::singleValueQuery("
                    SELECT GROUP_CONCAT(contact_id) 
                    FROM civicrm_activity_contact 
                    WHERE activity_id = {$objectId}
                      AND record_type_id = 3");
                if ($contact_ids) {
                    CRM_Contactsource_Contactsource::updateContactSourceField(explode(',', $contact_ids));
                }
            }

            if (empty($params['subject'])) {
                $params['subject'] = CRM_Contactsource_Contactsource::getContactSourceActivitySubject($params);
            }
        }
    }
}
