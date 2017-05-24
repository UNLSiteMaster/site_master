# Overrides

This is technical documentation for the overrides feature

SiteMaster supports an override function, which allows site developers to 'override' a notice, indicate that it is not actually a problem, and prevent it from appearing in future scans.

## feature expectations

* Who can add overrides? Any verifies site member or system admin
* What can be overridden? Any notice (mark with points deducted = 0.00)
* How do you add an override? click into the 'how to fix' screen for a notice and instructions will be at the bottom of the page
* How do you delete overrides? overrides will be listed in the site navigation, where they can then be deleted if you also have permissions to create overrides on the site
* Scope of overrides? 3 scopes: element, page, and site.
* When do overrides expire? After one year, but metrics can indicate that the notice does not require future manual reviews and thus won't expire. For example, redirects or spelling errors probably don't need future reviews, while PDF accessibility might.

Because of the nature of HTML, it might be impossible to reliably distingish matching elements in the future. The html context should work most of the time, but if the logic that populates the field ever changes, it will no longer match.
