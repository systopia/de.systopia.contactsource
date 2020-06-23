<?php
/*-------------------------------------------------------+
| SYSTOPIA Contact Source Extension                      |
| Copyright (C) 2020 SYSTOPIA                            |
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
 * Settings / configuration options for contact source
 */
class CRM_Contactsource_Form_Settings extends CRM_Core_Form
{

    public function buildQuickForm()
    {
        // synchronise contact source
        $this->add(
            'select',
            'contact_source_sync',
            E::ts("Copy to contact's source field"),
            [
                '' => E::ts("disabled"),
                'first_campaign' => E::ts("First contact (campaign)"),
                'first_subject' => E::ts("First contact (subject)"),
                'all_campaign' => E::ts("All contacts (campaign)"),
                'all_subject' => E::ts("All contacts (subject)"),
            ],
            FALSE
        );
        $this->add(
            'checkbox',
            'contact_source_sync_now',
            E::ts("Copy data now!")
        );

        // autofill source subject
        $this->add(
            'select',
            'contact_source_subject',
            E::ts("Fill Contact Source"),
            [
                '' => E::ts("don't"),
                'campaign_title' => E::ts("with campaign title")
            ],
            FALSE
        );
        $this->add(
            'checkbox',
            'contact_source_subject_now',
            E::ts("Fill Contact Source now!")
        );

        // add form elements
        $this->addButtons([
            [
                'type' => 'submit',
                'name' => E::ts('Apply'),
                'isDefault' => TRUE,
            ],
        ]);

        // set current values
        $this->setDefaults([
            'contact_source_sync' => Civi::settings()->get('contact_source_sync'),
            'contact_source_subject' => CRM_Contactsource_Configuration::getDefaultActivitySubject(),
        ]);

        parent::buildQuickForm();
    }

    public function postProcess()
    {
        // first: store values
        $values = $this->exportValues();
        Civi::settings()->set('contact_source_sync', CRM_Utils_Array::value('contact_source_sync', $values, ''));
        Civi::settings()->set('contact_source_subject', CRM_Utils_Array::value('contact_source_subject', $values, ''));

        // then: run any updates
        if (!empty($values['contact_source_subject_now'])) {
            $actvitites_updated = CRM_Contactsource_Contactsource::updateAllContactSourceActivitySubjects();
            CRM_Core_Session::setStatus(E::ts("%1 contact source activity subjects filled.", [1 => $actvitites_updated]));
        }
        if (!empty($values['contact_source_sync_now'])) {
            $actvitites_updated = CRM_Contactsource_Contactsource::updateContactSourceField();
            CRM_Core_Session::setStatus(E::ts("%1 contact source fields updated.", [1 => $actvitites_updated]));
        }
        parent::postProcess();
    }

}
