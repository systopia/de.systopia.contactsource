{* copied from https://github.com/CiviCooP/org.civicoop.xtendedcontactsource/blob/master/templates/CRM/Xtendedcontactsource/XtendedContactSource.tpl *}
<div class = "extended_contact_source-section">
  <div class="crm-summary-row ">
    <div class="crm-label">{ts domain='de.systopia.contactsource'}Contact Source{/ts}</div>
    <div class="crm-content crm-xtended-contact_source">{$contact_source_string}</div>
  </div>
</div>

{literal}
  <script>
    cj(function($){
      $(".crm-summary-contactinfo-block").children().children().children().append($(".extended_contact_source-section").html());
      $(".extended_contact_source-section").remove();
    });
  </script>
{/literal}