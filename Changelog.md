3.0.2 - January 13, 2024

The component now also works in debug mode.
Small fixes.

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
Changed how highlight is working. It's now uses `<mark>` instead of the `<span>`. No backward compat mode(not fully implemented in Joomla).
Model now add a log entry if mail not available for some reason.
Some component CSS removed because uses bootstrap CSS.
Minimum required Joomla version is 4.4.0.
