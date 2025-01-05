3.0.1 - January 5, 2024
Small fixes.

3.0.0 - December 30, 2024
Add compatibility with Joomla 5:
  - Behavior - Compatibility plugin is not required anymore;
  - bootstrap modal is used to display form;
  - bootstrap alert and toast is used to display errors and info;
  - added onProofreaderFormBeforeDisplay, onProofreaderFormAfterDisplay, onProofreaderFormPrepend, onProofreaderFormAppend events;
  - added hCaptcha, Google invisible reCaptcha, Cloudflare Turnstile captcha support. Only global captcha option is applied.
Router class is now J4/J5-style.
Changed how highlight is working. It's now uses <mark> instead of the <span>. No backward compat mode(not fully implemented in Joomla).
Model now add a log entry if mail not available for some reason.
Some component CSS removed because uses bootstrap CSS.
Minimum required Joomla version is 4.4.0.

2.1.0 - June 14, 2022
Add basic compatibility with Joomla 4:
  - modify SQL:
    - remove default values for text columns;
    - drop unused typo_raw column;
  - replace JResponse with JApplication;
  - add J4-style call for functions adding script and stylesheet;
  - add routing class (not J4-style but enough to make extension work);
  - remove Joomla3-style sidebar and filter in admin if run on Joomla 4, add Joomla4-style searchtools;
  - make "About" page in admin work with Joomla 4.
Fix bugs:
  - remove blank lines at the top and at the bottom of the document if highlight option is active;
  - prevent hidden form fields tags and attributes from being glued so they didn't work;
  - fix a bug with table overflow in admin on Joomla 3.
Add a feature: plugin works in debug mode even if the site if offline (useful for testing and development).
Update metadata.

2.1.1 - June 24, 2022
Fixes to pass JED Checker check:
  - remove _QQ_ in language files;
  - remove donations;
  - update copyrights, author names and email, remove author url in manifest files.

2.1.3 - November 11, 2022
Fix a SQL warning in Joomla 4.

2.1.4 - November 11, 2022
Add class to typo form header, group form elements info a div.proofreader_form_body. element

2.2 - May 14, 2024
Fix an issue with form display error in J5.

