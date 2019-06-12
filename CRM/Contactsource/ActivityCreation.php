<?php
/*-------------------------------------------------------+
| SYSTOPIA Contact Source Extension                      |
| Copyright (C) 2019 SYSTOPIA                            |
| Author: B. Zschiedrich (zschiedrich@systopia.de)       |
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
 * Class CRM_Uimods_ActivityCreation
 */
class CRM_Contactsource_ActivityCreation {

    /**
     * Perform actions on hook_civicrm_pageRun().
     *
     * @param $page
     */
    public static function pageRun(&$page) {
    }

    /**
     * Perform actions on hook_civicrm_buildForm().
     *
     * @param string $formName
     * @param CRM_Contact_Form_Contact $form
     */
    public static function buildForm($formName, &$form) {
    }

    /**
     * Perform actions on hook_civicrm_preProcess().
     *
     * @param string $formName
     * @param CRM_Contact_Form_Contact $form
     */
    public static function preProcess($formName, &$form) {
    }

    /**
     * Perform actions on hook_civicrm_postProcess().
     *
     * @param string $formName
     * @param CRM_Contact_Form_Contact $form
     */
    public static function postProcess($formName, &$form) {
    }
}
