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
   * Test if the contact source fields should be injected
   * @param CRM_Core_Form $form the form being rendered
   * @return bool should it be injected
   */
  public static function shouldInject($form) {
    if ($form->_action != CRM_Core_Action::ADD) {
      return false;
    }

    // TODO: setting to turn it off?
    return true;
  }


  /**
   * Perform actions on hook_civicrm_buildForm().
   *
   * @param string $formName
   * @param CRM_Contact_Form_Contact $form
   */
  public static function buildForm($formName, &$form) {

    // get campaigns
    $campaigns = self::getCampaigns();

    // extend form elements
    $form->add(
      'select',
      'contactorigin_campaign',
      ts('Campaign'),
      $campaigns,
      TRUE
    );
    $form->add(
        'text',
        'contactorigin_subject',
        ts('Information'),
        ['placeholder' => E::ts("Where and how?"), 'class' => 'huge'],
        TRUE
    );
    $form->add(
        'datepicker',
        'contactorigin_date',
        ts('First Contact Date'),
        [],
        TRUE,
        ['time' => FALSE]
    );
    $form->setDefaults([
        'contactorigin_campaign' => 0,
        'contactorigin_date'     => date('Y-m-d')
    ]);

    // inject template and script
    CRM_Core_Region::instance('page-body')->add(['template' => E::path("templates/CRM/Contactsource/Form/ActivityCreation.tpl")]);
    Civi::resources()->addScriptFile('de.systopia.contactsource', "js/ActivityCreation.js");
    Civi::resources()->addVars('contactsource', ['campaigns' => $campaigns]);
  }

  /**
   * Perform actions on hook_civicrm_postProcess().
   *
   * @param string $formName
   * @param CRM_Contact_Form_Contact $form
   */
  public static function postProcess($formName, &$form) {
    // create activity
    $values = $form->exportValues();

    // if date is today, use the time as well
    $datetime = $values['contactorigin_date'];
    if (date('Ymd', strtotime($values['contactorigin_date'])) == date('Ymd')) {
      $datetime = date('YmdHis');
    }

    // make sure '0' is not passed
    $campaign = $values['contactorigin_campaign'];
    if (empty($campaign)) {
      $campaign = '';
    }

    // create the contribution
    civicrm_api3('Activity', 'create', [
        'activity_date_time' => $datetime,
        'activity_type_id'   => CRM_Contactsource_Configuration::getActivityTypeID(),
        'campaign_id'        => $campaign,
        'status_id'          => 'Completed',
        'source_contact_id'  => CRM_Core_Session::getLoggedInContactID(),
        'target_id'          => $form->_contactId,
        'subject'            => $values['contactorigin_subject'],
    ]);
  }

  /**
   * List all campaigns that shall be shown in the dropdown menu.
   * @return array The campaign list in the form "id => title".
   */
  public static function getCampaigns() {

    $campaigns = civicrm_api3(
      'Campaign',
      'get',
        [
            'sequential'   => 1,
            'is_active'    => 1,
            'return'       => ["id", "title"],
            'option.limit' => 0
        ]
    );

    $campaignIdTitleMap = [
      0 => ts('No Campaign')
    ];

    foreach ($campaigns['values'] as $campaign) {
      $campaignIdTitleMap[$campaign['id']] = $campaign['title'];
    }

    return $campaignIdTitleMap;
  }
}
