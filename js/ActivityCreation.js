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

    let firstAccordion = accordionWrappers[0];

    if (!firstAccordion)
    {
        return;
    }

    accordionWrappers.addClass('collapsed');

    contactOriginWrapper.insertBefore(firstAccordion);

})(cj);