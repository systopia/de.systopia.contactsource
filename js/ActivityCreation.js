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

(function ($) {
    let contactOriginWrapper = $('#contactOriginWrapper');
    let accordionWrappers = $('.crm-accordion-wrapper').not(contactOriginWrapper);

    // make sure there's something there...
    let firstAccordion = accordionWrappers[0];
    if (!firstAccordion)
    {
        return;
    }

    // collapse other accordions, if subject is not filled yet
    if (cj("#contactorigin_subject").val()) {
        contactOriginWrapper.addClass('collapsed');
    } else {
        accordionWrappers.addClass('collapsed');
    }

    // insert ours at the top
    contactOriginWrapper.insertBefore(firstAccordion);

    // copy campaign title into subject if selected
    cj("#contactorigin_campaign").change(function() {
        let selected_campaign = cj("#contactorigin_campaign").val();
        if (parseInt(selected_campaign)) {
            cj("#contactorigin_subject")
                .val(CRM.vars.contactsource.campaigns[selected_campaign])
                .change();
        }
    });

    // open main contact when fields are filled
    cj("#contactorigin_subject").change(function() {
        if (cj("#contactorigin_subject").val()) {
            cj('.crm-accordion-wrapper')
                .not(contactOriginWrapper)
                .first()
                .removeClass('collapsed');
        }
    });
})(cj);