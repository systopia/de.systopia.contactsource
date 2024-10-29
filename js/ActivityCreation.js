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
  let accordionWrappers = $('details.crm-accordion-bold').not(contactOriginWrapper);

  // make sure there's something there...
  if (0 === accordionWrappers) {
    return;
  }

  // collapse other accordions, if subject is not filled yet
  if ($('#contactorigin_subject').val()) {
    contactOriginWrapper.addClass('collapsed');
  }
  else {
    accordionWrappers.addClass('collapsed').removeAttr('open');
  }

  // insert ours at the top
  contactOriginWrapper.insertBefore(accordionWrappers.first());

  // copy campaign title into subject if selected
  $('#contactorigin_campaign').change(function () {
    let selected_campaign = $('#contactorigin_campaign').val();
    if (parseInt(selected_campaign)) {
      $('#contactorigin_subject')
        .val(CRM.vars.contactsource.campaigns[selected_campaign])
        .change();
    }
  });

  // open main contact when fields are filled
  $('#contactorigin_subject').change(function () {
    if ($('#contactorigin_subject').val()) {
      $('.crm-accordion-wrapper')
        .not(contactOriginWrapper)
        .first()
        .removeClass('collapsed');
    }
  });
})(CRM.$ || cj);
