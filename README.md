# SYSTOPIA Contact Source Extension

CiviCRM features a ``source`` field with a contact. This field, however, has three basic flaws:
1. it's free text, so a later evaluation and statistics are tricky
2. if you merge duplicates, only one of the two source values will prevail, the other one is lost
3. The ``source`` field doesn't say anything about *when* this contact was generated

This extension aims to fix these three problems. It creates a new activity type ``First Contact`` to mark the event of the first encounter with the contact. Activities have a date, a campaign, and survive the merge process without losing data.

For convenience, this extension also features:
* you can either decide to override CiviCRM's ``source`` field *or* render a virtual source field in the contact summary with the new ``First Contact`` activity data
* a mandatory field set in the contact create form, so you are forced to provide this information
* statistics on your contact source activities can now be produced with the activity/campaign reports 
