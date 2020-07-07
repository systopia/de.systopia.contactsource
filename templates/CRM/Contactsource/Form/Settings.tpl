{*-------------------------------------------------------+
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
+--------------------------------------------------------*}

{crmScope extensionKey='de.systopia.contactsource'}
<h3>{ts}General Settings{/ts}</h3>
<br/>
<div class="crm-section">
  <div class="label">{$form.contact_source_inject_form.label}</div>
  <div class="content">{$form.contact_source_inject_form.html}</div>
  <div class="clear"></div>
</div>
<div class="crm-section">
  <div class="label">{$form.contact_source_inject_field.label}</div>
  <div class="content">{$form.contact_source_inject_field.html}</div>
  <div class="clear"></div>
</div>

<h3>{ts}Fill Contact's Source Field{/ts}</h3>
<br/>
<div class="crm-section">
  <div class="label">{$form.contact_source_sync.label}</div>
  <div class="content">{$form.contact_source_sync.html}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.contact_source_sync_now.label}</div>
  <div class="content">{$form.contact_source_sync_now.html}</div>
  <div class="clear"></div>
</div>

<h3>{ts}Fill Contact Source Activity Subject{/ts}</h3>
<br/>
<div class="crm-section">
  <div class="label">{$form.contact_source_subject.label}</div>
  <div class="content">{$form.contact_source_subject.html}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.contact_source_subject_now.label}</div>
  <div class="content">{$form.contact_source_subject_now.html}</div>
  <div class="clear"></div>
</div>

<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}