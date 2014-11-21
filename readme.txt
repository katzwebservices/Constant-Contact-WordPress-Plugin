=== Constant Contact for Wordpress ===
Contributors: katzwebdesign, katzwebservices
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=zackkatz%40gmail%2ecom&item_name=Constant%20Contact%20API%20Plugin&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: mail, email, newsletter, Constant Contact, plugin, sidebar, widget, mailing list, API, email marketing, newsletters, form, forms, event, events, event marketing
Requires at least: 3.3
Tested up to: 4.0.1
Stable tag: 3.1.3

Integrate Constant Contact into your website with this full-featured plugin.

== Description ==

> __This plugin requires a <a href="http://wordpress.constantcontact.com" title="Sign up for a free Constant Contact trial" rel="nofollow">Constant Contact account</a>.__ <br />*Don't have an account?* Constant Contact offers a <a href="http://wordpress.constantcontact.com/email-marketing/index.jsp" rel="nofollow">free 60 day trial</a>, so sign up and give this plugin a whirl!

<h4>Fully integrate Constant Contact with your WordPress website.</h4>

The Constant Contact for Wordpress plugin is the best email marketing plugin for WordPress: integrate your website seamlessly with your Constant Contact account.

You can place a signup checkbox or list selection on your register page or use the signup widget anywhere in your website sidebar or PHP templates.

<h3>Event Marketing</h3>
The plugin features <a href="http://wordpress.constantcontact.com/event-marketing/index.jsp" title="Learn more about Constant Contact Event Marketing" rel="nofollow">Constant Contact Event Marketing</a> functionality by allowing you to track events, registration, and registrants using the plugin. Simply navigate to Constant Contact > Events. Manage your events from inside WordPress!

<h3>Built-in Form Designer</h3>
<strong>The Constant Contact Form Designer</strong> is a form generation and design tool. The Form Designer allows users to generate unlimited number of unique forms and gives a wide variety of options that can be configured, including what fields to show in the signup form. There and tons of design options, including custom background images, border width, colors, fonts and much more.

<h3>Constant Analytics: In-Depth Google Analytics</h3>
View your Google Analytics data in your dashboard with Constant Analytics. View traffic by source, geography, and popularity. See the impact of blog posts and email campaigns with the great graphing tools.

<h4>Plugin features:</h4>
* Add signup checkbox and list selection to your register page and update profile page
* Add / edit contact lists without visiting constantcontact.com
* Includes a powerful form designer
* Built-in Google Analytics visualization
* View your events registration details and get updated with a dashboard widget
* Show contact list selection on register page with ability to exclude certain lists
* Automatically subscribe your user to one or more contact lists on the register page
* Customize the register page signup box (and list selection) title and description
* Add / edit users from your constant contact account
* Add a signup widget to your sidebar or anywhere in your template

<h4>Plugin Support</h4>
To obtain support please use this link to the [wordpress forums](http://wordpress.org/tags/constant-contact-api).

<h4>If you like the plugin...</h4>
If you use the plugin and find it useful please make sure to come back and vote so other users know it works.

== Installation ==

To install the plugin follow the steps below:

1. Upload `ctct` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Activate the Constant Contact API: Form Designer plugin (optional)
4. Click the new main menu item called "Constant Contact".
5. You'll need to enter your username and password on the settings page then save the page to see your contact lists.
6. Now Configure the "Register Page Settings" to get the checkbox or list selection displayed on the user register page.

### Using the Form Designer
1. Install this plugin.
2. Activate the Constant Contact API: Form Designer plugin
3. Configure the settings on the form designer by updating the settings in the boxes on the left.
4. Next to "Form Name" where it says "Enter form name here," enter your form name.
5. Once you have configured and named your form, click Save Form.
6. In the Appearance menu of the administration, click the Widgets link.
7. Drag the widget named "Constant Contact Form Designer" into the sidebar.
8. Configure the settings shown, then click the "Save" button at the bottom of the widget form.
9. You will see the signup widget you created on your website!
10. To edit the form, return the the Form Designer page (from Step 3) and click on the form tab with the name of the form you would like to edit. Edit the form, then click Update Form. The form will show as updated on your website.

== Changelog ==

= Version 3 Requires PHP 5.3 =
__Version 3.x changes requirements for your server. If you upgrade and the upgrade doesn't work for you, you can downgrade to the previous version of the plugin.__

= 3.1.3 = 
* Update translation textdomain to `ctct` from `constant-contact-api`
* Hide the custom content editor until checked
* Fix default label size

= 3.1.2 =
* Delete old username and password on activation
* When plugin is de-activated, delete stored token and transients
* Update InlineEdit script
* Verify plugin status for Akismet and WangGuard plugins
* Update DataValidation.com API integration
* Add `blank` parameter to `KWSContactList::outputHTML()` for select dropdowns that need an empty option
* Update strings

= 3.1.1 =
* Redesigned Form Designer to match WP 4.0 look & feel
	* Added input sliders to Form Designer - easier than dropdowns
	* Used accordion instead of metaboxes
	* Use Dashicons for Bold/Italic instead of images
* Use Dashicons for Edit pencil
* Updated translation strings
* Converted much CSS to LESS
* Refactored `form-designer-functions.php` as `CTCT_Form_Designer_Helper` class

= 3.1 = 
* Rewrote form designer
* Rewrote form output
* Tons of updates

= 3.0.4 = 
* Added JS localization to fix inline edit screens
* Fixed: Error 500 with form live preview
* Fixed: many PHP warnings
* Fixed: Added security validation when updating / deleting forms.
* Fixed: logging toggle not toggling
* Moved SafeSubscribe to Form Fields metabox in Form Designer
* Removed Exceptional.io integration
* Only load `CTCT_Admin_Page` in admin. Saves memory and improves speed.
* Added: Zebra-striping on tables
* Improved: Design for WP 3.8+
* Removed: Menu when hovering-over logo
* Changed icon images to dashicons
* Fixed: Converted jQuery `.live()` to `.on()`
* Added: Internationalization strings & improvements
* Added: Disabled `<input>` fields to select events shortcode, instead of text. Should make it easier to grab the code.
* Converted plugin version constant to class constants
* Fixed: Campaigns limit fatal error: 50 not 500 are allowed.
* Fixed: "Nothing changed" message when ajax editing Contact fields
* Added: Improved logging with new Activity Log page
	- Use actions `ctct_debug` for helpful debugging output, `ctct_error` for errors or caught exceptions, and `ctct_activity` for Constant Contact REST calls.
	- Added logging settings to main settings page to specify which types of actions get logged
* Fixed: `.updated` `div`s were being removed by the plugin's help tabs 
* Fixed: Removed extra help tabs
* Fixed: Help tab labels now start with "Constant Contact:" when not on the plugin pages
* Fixed: CSS / JS loading on all admin pages
* Removed `/docs/` directory
* Add `no_events_text` parameter for shortcode (and widget setting) - allows you to define what text is shown when there are no events.
* Added option to define `0` as the `limit` parameter to show all events
* Sanitized widget output
* Fixed: Insert events widget code would add lots of extra spaces to the shortcode
* Added: `onlyactive` checkbox to widget (previously it was always on)
* Added: Multiple event support, event limit selector, and `onlyactive` checkbox to insert event shortcode generator

= 3.0.3 =
* Now uses Composer to auto-load classes
* Updated to fix compatibility issues with changes to CTCT PHP SDK
* Improved deleting of settings and de-authentication
* Fixed Events shortcode echoing output instead of returning output
* Removed Pointers implementation for now.

= 3.0.1 =
### Completely re-written plugin

* The biggest thing: moves the plugin over to the new Constant Contact API.
	* Now uses oAuth 2 authentication instead of passwords
* Inline editing of Contact & List details
* Improved documentation
	* Plugin now supports the WordPress "Help" tab
	* There are some intro tour pointers to show new users how to use the plugin
* Subscribe to Newsletter checkbox on Comment forms
* Removed Extra Field Mappings
* Lots of Form Designer functionality
	- New option for using style or not
	- Now respects Submit form position
	- Includes options for transparent backgrounds and no borders
	- Includes better theme support
		- Includes classes for themes that support Gravity Forms
		- Includes support for WooThemes, Pagelines and Genesis frameworks
* Requires WordPress 3.3 or Higher

= 2.4.1 - January 31, 2013 =
* Fixed: issue with WordPress registration: the "Hidden Contact Lists" lists were not being properly added (the list IDs were incorrect), causing issues.
* Added: "Default Option Text" registration option, visible when using a Dropdown List "List Selection Format". Allows you to set the default text of the dropdown list.

= 2.4.0.3 - January 29, 2013 =
* The "You do not have sufficient permissions to access this page." error was caused by the plugin not reporting Error 403: Account Locked on the settings page. This release adds that error code.
* Consolidated dupe `session_start()` function
* Form Designer menu option now does not display if the plugin is not properly configured
* Updated Form Designer page permissions to `manage_options` instead of `administrator`

= 2.4.0.2 - January 28, 2013 =
* Possibly fixed issue with "You do not have sufficient permissions to access this page." error <a href="http://wordpress.org/support/topic/you-do-not-have-sufficient-permissions-to-access-this-page-136?replies=4">as reported in the support forum</a>.

= 2.4.0.1 =
* Possibly fixed issue with login not working on IIS sites

= 2.4.0 =
* Fixed some potential XSS vulnerabilities. Thanks to <a href="http://ultimorender.com.ar/funkascript">Manuel</a> for making them known.
* Form Designer
	* Updated to the latest TinyMCE codebase for the Form Editor. Unfortunately, the plugin still can't use the WP editor due to technical limitations.
	* Switched to using built-in jQuery and jQuery UI scripts for better speed and support going forward
	* Fixed lots of bugs
	* Fixed scrollFollow
	* Added some authentication to prevent hacking
* Fixed link to import FAQ
* Improved internationalization support plugin-wide
* Fixed: "View Activity" link now works to view individual activity items.
* Fixed Form Designer Widget extra options visibiltiy bug
* Improved admin styling for WordPress 3.5
* Removed Export page. Didn't make sense to have in there.

= 2.3.12 =
* Fixed issue introduced in 2.3.11 where settings were printed in the legacy widget.

= 2.3.11 =
* Fixed issue introduced in 2.3.10 where First Name and Last Name checkboxes don't stay checked in the Legacy Form Widget

= 2.3.10 =
* Form Designer Updates:
	* Added: Set the lists a form subscribes to in the Form Designer
	* Fixed: Ability to edit existing forms
	* Fixed: Issue where saving an existing form will jump to a different form
	* Added: A new form ID generator will prevent overlapping IDs when deleting forms
* Fixed: Constant Analytics once again allows you to pick from your Google Analytics profiles
* Modified: Removed "Add Campaign" button: the feature wasn't ready, and was included by mistake
* Improved Events list to include shortcode instead of unclear `id`
* Added `[eventspot]` shortcode. Previous `[ccevents]` shortcode still works.
* Added internationalization (translation support) to the Legacy Form widget.
* Added field for submit button text to the Legacy Form widget.

= 2.3.9 =
* Fixed Form Designer issue affecting being able to select active form
	* Added "Clear All Forms link" in top-right of Form Designer page to delete all forms
* If Event Location is empty, widget will not show "Location:" text

= 2.3.8 =
* Added Akismet spam filtering
* Fixed many issues with the Form Designer
	- Selected a form by default
	- Fixed broken Form Text field updating process
	- Fixed live updating for Form Alignment settings
	- Fixed issue with required fields not working
	- Added support for text color in the Form Text box
	- Added confirmation before deleting a form
* Added an unique `id` to each form for valid HTML on pages with multiple forms
* Made General Interest list not editable, since editing doesn't work anyway
* CC password now a text field to help password entry
* Changed how `session_start()` is called; should fix a potential bug

= 2.3.7 =
* Fixed fatal error for users with PHP versions not supporting `checkdnsrr` function for email domain validation.

= 2.3.6 =
* Resolved issue with Constant Analytics data not pulling
* Fixed issue with empty Form Designer submit button values
* Fixed fatal error for Form Designer `syntax error, unexpected '}' in /constant-contact-api/form-designer-meta-boxes.php on line 768

= 2.3.5.1 =
* Fixed one more fatal error

= 2.3.5 =
* Fixed one more fatal error
* WordPress download doesn't include some files. Returning back to `tag` instead of `trunk` to see if that works.

= 2.3.4 =
* Fixed issue with `constant-analytics.php` causing PHP warning
* Trying to get WordPress to notice new version!!!!

= 2.3.3 =
* Fixed fatal error with plugin. Sorry, folks!

= 2.3.2 =
* Fixed major issues with `[constantcontactapi]` shortcode not parsing passed attributes.
* Fixed bug where submitting a form with empty email field showed success
* Added a check for valid email domain name. This will prevent some fake email addresses (such as asdasdsa@adsamlcsac.com)
* Added a shortcode hint in the Form Designer
* When Form Designer forms are submitted and errors exist, submitted data is now filled into the forms. Users won't need to fill out the form again.
* Removed ability to delete users & lists, since it doesn't work for some reason
* Fixed some Form Designer bugs
	- Now Form Field checkboxes stay checked
	- When saving a form, the error message no longer displays
	- Submit button text now updates in the form preview.
	- Added a background & border to the Form Slide so that it's obvious why clicking doesn't work when Slide is turned on.
	- If a form exists, it is selected to edit by default (instead of Create New Form). This should help prevent confusion and duplicate forms.
* Added check for configuration to Constant Analytics dashboard
* Technical
	- Wrapped the plugin startup actions & hooks in a `plugins_loaded` hook
	- Removed config.php file; wasn't necessary
	- Removed redundant & unused code from Form Designer

= 2.3.1 =
* Updated Constant Analytics Javascript for WordPress 3.2 compatibility
* Fixed some not-so-minor issues
	* Fixed issue with legacy widget where hidden lists were not being added to the form properly
	* Fixed `Fatal error: Call to undefined method WP_Http::_getTransport() in .../constant-contact-api/constant-analytics.php on line 624` (<a href="http://wordpress.org/support/topic/604275">issue 604275</a>)
* Squashed minor bugs, notices and errors
	* Removed word "Array" some users are seeing at the top of their Form Designer forms (<a href="http://wordpress.org/support/topic/597622">issue 597622</a>)
	* Fixed `Invalid argument supplied for foreach() .../constant-contact-api/functions.php on line 844` notice (<a href="http://wordpress.org/support/topic/596160">issue 596160</a>)
	* Fixed `Undefined index: lists`, `exclude_lists` in legacy widget
	* Fixed issue where if shown contact lists were the same as hidden lists, an empty select input or list may be shown.
	* Fixed `Notice: rss.php is deprecated since version 3.0!` notice

= 2.3 =
* Added Contacts capability
	- <strong>Add new contacts from inside WordPress</strong>
	- View and edit all your contacts
	- Edit contact information, subscribed lists, etc.
	- View all the contacts in a list
* Added Constant Analytics, a great Google Analytics statistics tool that helps you visualize your blog traffic as well as blog posts and email campaigns in the same chart.
	- Switch between Google Analytics profiles & accounts
* New feature: hover over the Constant Contact logo to easily switch between plugin pages.
* Improved plugin load time on the administration
* Update should fix issue where updating users from the User administration page doesn't work properly (<a href="http://wordpress.org/support/topic/566686" rel="nofollow">issue 566686</a>)
* Added a hook for internationalization of plugin (multiple language support)
* Fixed issue where updating username & password settings may not change account except when closing browser window. This bug also affected if users de-activated plugin with the `Remove all data stored in database` selected. If users re-activated the plugin, the plugin would still seem to be configured properly because this information was stored in the browser session.

= 2.2 =
* Added an Events widget and shortcode
* Updated the readme to have Form Designer shortcode instructions (see FAQ)

= 2.1.4 =
* Converted the plugin to using the <a href="http://codex.wordpress.org/HTTP_API" rel="nofollow">WordPress HTTP API</a> and `wp_remote_request()`. This should fix issues some users have been having with setting up the plugin (such as <a href="http://wordpress.org/support/topic/565047" rel="nofollow">issue #565047</a>)
* Fixed issue where if the Constant Contact username & password settings were incorrect, then saved again (and still incorrect), there would be an error `Warning: Cannot modify header information - headers already sent by...`
* Improved error messages so you'll know whether Constant Contact is having an issue of if it's a settings configuration issue.

= 2.1.3 =
* Fixed issues with legacy widget not updating "Show First Name" and "Show Last Name" settings (<a href="http://wordpress.org/support/topic/548028" rel="nofollow">issue #548028</a>)
* Improved legacy widget to show "More info" content and reflect changes to "Show List Selection?" checkbox setting
* Fixed "Invalid Argument" Line 183 error (<a href="http://wordpress.org/support/topic/547609" rel="nofollow">issue #547609</a>)
* Fixed issue with forms not redirecting upon success (<a href="http://wordpress.org/support/topic/547609" rel="nofollow">issue #547609</a>)

= 2.1.2 =

* Form Designer
	* Determined that issues with Form Designer not displaying are caused by hosting configuration issues. <strong>Contact your web host and request that they "whitelist your domain for ModSecurity."</strong> View the FAQ section for more information.
	* Improved error notices for Form Designer when hosting issues are detected.
	* Improved the form generator javascript
* Legacy (non-Form Designer) widget
	* Improved speed
	* Fixed issue with "Please select at least 1 list" when Show List Selection was not checked
	* Restored functionality: incomplete form submissions once again fill in submitted data
* WP Registration form
	* Added support for Multisite registration forms
	* Vastly improved registration form functionality, including formatting of description, labels, and more.
	* Fixed <a href="http://wordpress.org/support/topic/432029">bug #432029</a>; "subscribe users by default" now functions properly when using the single opt-in checkbox
	* Added multiple filters to modify registration form, including `constant_contact_register_form`; you can now modify entire output of plugin on the registration page by using `add_filter('constant_contact_register_form', 'your_function_to_modify');`. <a href="http://codex.wordpress.org/Function_Reference/add_filter" rel="nofollow">Learn more about `add_filter` on WordPress.org</a>.
* Events - restored Events functionality that got messed up in 2.1.1.

= 2.1.1 =
* Improved Events page layout by adding Event Status filters and updating styles
* Added Events dashboard widget showing active & draft events

= 2.1 =
* Events Marketing page now available in the administration (under Constant Contact > Events)
	* View event and registrant details
* Improves speed of administration by caching Activities, Campaigns, Lists, and Events

= 2.0.1 =
* <strong>Fixed major bug</strong> where username and password would be reset when saving settings on the plugin's Registration options page. (<a href="http://wordpress.org/support/topic/532274" rel="nofollow">issue #532274</a>)
* Restored options to show or hide first and last names in Legacy widget (<a href="http://wordpress.org/support/topic/532932" rel="nofollow">issue #532932</a>)
* Fixed multiple Legacy widget bugs
* Remedied bug where registration form description wasn't displaying (<a href="http://wordpress.org/support/topic/513878" rel="nofollow">issue #513878</a>)
* Improved blog registration form HTML
* Improved Admin Profile lists HTML

= 2.0 =
* <strong>Major upgrade</strong> - make sure to back up your database. If you already have installed the plugin, this upgrade may not transfer your current settings.
* Went through each page of the admin and made the layout and code better, and reworded the administration to <strong>make more sense</strong>
* Fixed Import, Export, Activity
* Converted the widget settings to be in the widget, not on a page.
* <strong>New Form Designer</strong> - Create awesome forms with tons of configuration options. This is really cool. <em>Requires a decent browser for the admin. Internet Explorer older than 2009 won't work.</em> Please leave feedback with issues - this feature is in Alpha.
	* Drag and drop inputs with live-updating form preview
	* Create custom gradients or choose from patterns or URL-based backgrounds
	* So, so much more.
* Lists will now be updated (<a href="http://wordpress.org/support/topic/423429">bug #423429</a>)
* Added a sample import CSV file in the plugin folder, named `email-import-sample.txt`
* Improved load time of the plugin & widget

= 1.1.2 =
* Minor bug fix, fixes `in_array(): Wrong datatype for second argument` error <a href="http://wordpress.org/support/topic/393359" rel="nofollow">reported here</a>.
* Added menu image for plugin, and forced plugin name to be on one line. Menu looks nicer now.
* If plugin is not configured, the other menu items (Activities, Import, Export, Lists) will not be displayed. Previously, they were displayed, but the pages were empty.

= 1.1.1 =
* Files updated: constant-contact-api-widget.php, readme.txt, /admin/options.php
* <em>Short story:</em> __Improved speed.__ <br /><em>Long story:</em> Fixes major potential bug - if you have noticed your site takes a long time to start loading, it may be because the plugin had been trying to access the Constant Contact API for the list values twice per page load. This structure has been totally revamped, and now the Constant Contact API is only accessed once upon changing settings. This release improves load time considerably by storing that information in the WordPress database. Added `cc_widget_lists_array` option to store Constant Contact lists, so that the API doesn't need to be called every page load. Now, API is only called when the plugin settings are saved.
* Wrapped the List Selection Title for the multi-select form element in a `label` tag, and removed line break.

= 1.1.0.1 =
* Removed line break (`<br />`) before widget form to improve display of widget signup form
* Fixed widget description and title display issues by renaming variables from `$title` to `$widget_title` and `$description` to `$widget_description`.
* Converted some settings fields to `<textarea>` to make editing easier.

= 1.1 =
* Adds error messages if username & password aren't properly configured & working
* Replaced $_SESSION with $GLOBALS for servers with `register_globals` issues
* Improved widget error messages
	* Converted widget errors to list items (`<LI>`s), instead of items separated with `<BR />` for better standards compliance
	* Wrapped errors in `<LABEL>`s so that clicking an error will take users to the input
* Improved redirection upon widget submission; now properly redirects to the page the user was on, instead of the home page
* Added filters for more control over widget output:
	* apply_filters('constant_contact_form', $output); to widget output
	* apply_filters('constant_contact_form_success', $success);
	* apply_filters('constant_contact_form_description', $description);
	* apply_filters('constant_contact_form_errors', $errors);
	* apply_filters('constant_contact_form_submit', $submit_button);

= 1.0.10 =
* This release fixes a problem with 1and1 servers

= 1.0.7 =
* Problem with files in last release

= 1.0.6 =
* No code changes have been made in this release

= 1.0.5 =
* Fixed a bug relating to chunked http encoding in class.cc.php

== Upgrade Notice ==

= 2.4.1 =
* Fixed: issue with WordPress registration: the "Hidden Contact Lists" lists were not being properly added (the list IDs were incorrect), causing issues.
* Added: "Default Option Text" registration option, visible when using a Dropdown List "List Selection Format". Allows you to set the default text of the dropdown list.

= 2.4.0.3 =
* The "You do not have sufficient permissions to access this page." error was caused by the plugin not reporting Error 403: Account Locked on the settings page. This release adds that error code.
* Consolidated dupe `session_start()` function
* Form Designer menu option now does not display if the plugin is not properly configured
* Updated Form Designer page permissions to `manage_options` instead of `administrator`

= 2.4.0.2 =
* Possibly fixed issue with "You do not have sufficient permissions to access this page." error <a href="http://wordpress.org/support/topic/you-do-not-have-sufficient-permissions-to-access-this-page-136?replies=4">as reported in the support forum</a>.

= 2.4.0.1 =
* Possibly fixed issue with login not working on IIS sites

= 2.4.0 =
* Fixed some potential XSS vulnerabilities. Thanks to <a href="http://ultimorender.com.ar/funkascript">Manuel</a> for making them known.
* Form Designer
	* Updated to the latest TinyMCE codebase for the Form Editor. Unfortunately, the plugin still can't use the WP editor due to technical limitations.
	* Switched to using built-in jQuery and jQuery UI scripts for better speed and support going forward
	* Fixed lots of bugs
	* Fixed scrollFollow
	* Added some authentication to prevent hacking
* Fixed link to import FAQ
* Improved internationalization support plugin-wide
* Fixed: "View Activity" link now works to view individual activity items.
* Fixed Form Designer Widget extra options visibiltiy bug
* Improved admin styling for WordPress 3.5
* Removed Export page. Didn't make sense to have in there.

= 2.3.11 =
* Fixed issue introduced in 2.3.10 where First Name and Last Name checkboxes don't stay checked in the Legacy Form Widget

= 2.3.10 =
* Form Designer Updates:
	* Added: Set the lists a form subscribes to in the Form Designer
	* Fixed: Ability to edit existing forms
	* Fixed: Issue where saving an existing form will jump to a different form
	* Added: A new form ID generator will prevent overlapping IDs when deleting forms
* Fixed: Constant Analytics once again allows you to pick from your Google Analytics profiles
* Modified: Removed "Add Campaign" button: the feature wasn't ready, and was included by mistake
* Improved Events list to include shortcode instead of unclear `id`
* Added `[eventspot]` shortcode. Previous `[ccevents]` shortcode still works.
* Added internationalization (translation support) to the Legacy Form widget.
* Added field for submit button text to the Legacy Form widget.

= 2.3.9 =
* Fixed Form Designer issue affecting being able to select active form
	* Added "Clear All Forms link" in top-right of Form Designer page to delete all forms
* If Event Location is empty, widget will not show "Location:" text

= 2.3.8 =
* Added Akismet spam filtering
* Fixed many issues with the Form Designer
	- Selected a form by default
	- Fixed broken Form Text field updating process
	- Fixed live updating for Form Alignment settings
	- Fixed issue with required fields not working
	- Added support for text color in the Form Text box
	- Added confirmation before deleting a form
* Added an unique `id` to each form for valid HTML on pages with multiple forms
* Made General Interest list not editable, since editing doesn't work anyway
* CC password now a text field to help password entry
* Changed how `session_start()` is called; should fix a potential bug

= 2.3.6 =
* Resolved issue with Constant Analytics data not pulling
* Fixed issue with empty Form Designer submit button values
* Fixed fatal error for Form Designer `syntax error, unexpected '}' in /constant-contact-api/form-designer-meta-boxes.php on line 768

= 2.3.5.1 =
* Fixed one more fatal error

= 2.3.5 =
* Fixed one more fatal error
* WordPress download doesn't include some files. Returning back to `tag` instead of `trunk` to see if that works.

= 2.3.4 =
* Fixed issue with `constant-analytics.php` causing PHP warning
* Trying to get WordPress to notice new version!!!!

= 2.3.3 =
* Fixed fatal error with plugin. Sorry, folks!

= 2.3.2 =
* Fixed major issues with `[constantcontactapi]` shortcode not parsing passed attributes.
* Fixed bug where submitting a form with empty email field showed success
* Added a check for valid email domain name. This will prevent some fake email addresses (such as asdasdsa@adsamlcsac.com)
* Added a shortcode hint in the Form Designer
* When Form Designer forms are submitted and errors exist, submitted data is now filled into the forms. Users won't need to fill out the form again.
* Removed ability to delete users & lists, since it doesn't work for some reason
* Fixed some Form Designer bugs
	- Now Form Field checkboxes stay checked
	- When saving a form, the error message no longer displays
	- Submit button text now updates in the form preview.
	- Added a background & border to the Form Slide so that it's obvious why clicking doesn't work when Slide is turned on.
	- If a form exists, it is selected to edit by default (instead of Create New Form). This should help prevent confusion and duplicate forms.
* Added check for configuration to Constant Analytics dashboard
* Technical
	- Wrapped the plugin startup actions & hooks in a `plugins_loaded` hook
	- Removed config.php file; wasn't necessary
	- Removed redundant & unused code from Form Designer

= 2.3.1 =
* Updated Constant Analytics Javascript for WordPress 3.2 compatibility
* Fixed some not-so-minor issues
	* Fixed issue with legacy widget where hidden lists were not being added to the form properly
	* Fixed `Fatal error: Call to undefined method WP_Http::_getTransport() in .../constant-contact-api/constant-analytics.php on line 624` (<a href="http://wordpress.org/support/topic/604275">issue 604275</a>)
* Squashed minor bugs, notices and errors
	* Removed word "Array" some users are seeing at the top of their Form Designer forms (<a href="http://wordpress.org/support/topic/597622">issue 597622</a>)
	* Fixed `Invalid argument supplied for foreach() .../constant-contact-api/functions.php on line 844` notice (<a href="http://wordpress.org/support/topic/596160">issue 596160</a>)
	* Fixed `Undefined index: lists`, `exclude_lists` in legacy widget
	* Fixed issue where if shown contact lists were the same as hidden lists, an empty select input or list may be shown.
	* Fixed `Notice: rss.php is deprecated since version 3.0!` notice

= 2.3 =
* Added Contacts capability
	- <strong>Add new contacts from inside WordPress</strong>
	- View and edit all your contacts
	- Edit contact information, subscribed lists, etc.
	- View all the contacts in a list
* Added Constant Analytics, a great Google Analytics statistics tool that helps you visualize your blog traffic as well as blog posts and email campaigns in the same chart.
	- Switch between Google Analytics profiles & accounts
* New feature: hover over the Constant Contact logo to easily switch between plugin pages.
* Improved plugin load time on the administration
* Update should fix issue where updating users from the User administration page doesn't work properly (<a href="http://wordpress.org/support/topic/566686" rel="nofollow">issue 566686</a>)
* Added a hook for internationalization of plugin (multiple language support)
* Fixed issue where updating username & password settings may not change account except when closing browser window. This bug also affected if users de-activated plugin with the `Remove all data stored in database` selected. If users re-activated the plugin, the plugin would still seem to be configured properly because this information was stored in the browser session.

= 2.2 =
* Added an Events widget and shortcode
* Updated the readme to have Form Designer shortcode instructions (see FAQ)

= 2.1.4 =
* Converted the plugin to using the <a href="http://codex.wordpress.org/HTTP_API" rel="nofollow">WordPress HTTP API</a>. This should fix issues some users have been having with setting up the plugin (such as <a href="http://wordpress.org/support/topic/565047" rel="nofollow">issue #565047</a>)
* Fixed issue where if the Constant Contact username & password settings were incorrect, then saved again (and still incorrect), there would be an error `Warning: Cannot modify header information - headers already sent by...`

= 2.1.3 =
* Fixed issues with legacy widget not updating "Show First Name" and "Show Last Name" settings (<a href="http://wordpress.org/support/topic/548028" rel="nofollow">issue #548028</a>)
* Improved legacy widget to show "More info" content and reflect changes to "Show List Selection?" checkbox setting
* Fixed "Invalid Argument" Line 183 error (<a href="http://wordpress.org/support/topic/547609" rel="nofollow">issue #547609</a>)
* Fixed issue with forms not redirecting upon success (<a href="http://wordpress.org/support/topic/547609" rel="nofollow">issue #547609</a>)

= 2.1.2 =

* Form Designer
	* Determined that issues with Form Designer not displaying are caused by hosting configuration issues. <strong>Contact your web host and request that they "whitelist your domain for ModSecurity."</strong> View the FAQ section for more information.
	* Improved error notices for Form Designer when hosting issues are detected.
	* Improved the form generator javascript
* Legacy (non-Form Designer) widget
	* Improved speed
	* Fixed issue with "Please select at least 1 list" when Show List Selection was not checked
	* Restored functionality: incomplete form submissions once again fill in submitted data
* WP Registration form
	* Added support for Multisite registration forms
	* Vastly improved registration form functionality, including formatting of description, labels, and more.
	* Fixed <a href="http://wordpress.org/support/topic/432029">bug #432029</a>; "subscribe users by default" now functions properly when using the single opt-in checkbox
	* Added multiple filters to modify registration form, including `constant_contact_register_form`; you can now modify entire output of plugin on the registration page by using `add_filter('constant_contact_register_form', 'your_function_to_modify');`. <a href="http://codex.wordpress.org/Function_Reference/add_filter" rel="nofollow">Learn more about `add_filter` on WordPress.org</a>.
* Events - restored Events functionality that got messed up in 2.1.1.

= 2.1.1 =
* Improved Events page layout by adding Event Status filters and updating styles
* Added Events dashboard widget showing active & draft events

= 2.1 =
* Events Marketing page now available in the administration (under Constant Contact > Events)
	* View event and registrant details
* Improves speed of administration by caching Activities, Campaigns, Lists, and Events

= 2.0.1 =
* <strong>Fixed major bug</strong> where username and password would be reset when saving settings on the plugin's Registration options page. (<a href="http://wordpress.org/support/topic/532274" rel="nofollow">issue #532274</a>)
* Restored options to show or hide first and last names in Legacy widget (<a href="http://wordpress.org/support/topic/532932" rel="nofollow">issue #532932</a>)
* Fixed multiple Legacy widget bugs
* Remedied bug where registration form description wasn't displaying (<a href="http://wordpress.org/support/topic/513878" rel="nofollow">issue #513878</a>)
* Improved blog registration form HTML
* Improved Admin Profile lists HTML

= 2.0 =
* <strong>Major upgrade</strong> - make sure to back up your database. If you already have installed the plugin, this upgrade may not transfer your current settings.
* Went through each page of the admin and made the layout and code better, and reworded the administration to <strong>make more sense</strong>
* Fixed Import, Export, Activity
* Converted the widget settings to be in the widget, not on a page.
* <strong>New Form Designer</strong> - Create awesome forms with tons of configuration options. This is really cool. <em>Requires a decent browser for the admin. Internet Explorer older than 2009 won't work.</em> Please leave feedback with issues - this feature is in Alpha.
	* Comes with a new Form Designer widget
	* Drag and drop inputs with live-updating form preview
	* Create custom gradients or choose from patterns or URL-based backgrounds
	* So, so much more. Check out the screenshots for an example.
* Lists will now be updated (<a href="http://wordpress.org/support/topic/423429">bug #423429</a>)
* Added a sample import CSV file in the plugin folder, named `email-import-sample.txt`
* Improved load time of the plugin & widget

= 1.1.2 =
* Minor bug fix, fixes `in_array(): Wrong datatype for second argument` error <a href="http://wordpress.org/support/topic/393359" rel="nofollow">reported here</a>.

= 1.1.1 =
* Fixes major potential bug - if you have noticed your site takes a long time to start loading, it may be because the plugin had been trying to access the Constant Contact API for the list values twice per page load. This structure has been totally revamped, and now the Constant Contact API is only accessed once upon changing settings. This release improves load time considerably by storing that information in the WordPress database.
* Added `cc_widget_lists_array` option to store Constant Contact lists, so that the API doesn't need to be called every page load. Now, API is only called when the plugin settings are saved.

= 1.1.0.1 =
* If your widget description and titles were not displaying in the signup form, that is now fixed.

= 1.1 =
* Fixes a potential `register_globals` issue
* Adds helpful filters for developers
* Improves widget error handling & error messages
* Improves

= 1.0.10 =
This release fixes a problem with 1and1 servers

= 1.0.7 =
This version fixes a bug introduced in version 0.0.5.

= 1.0.6 =
This version simply updates the readme.txt file so the project description page is more useful.

= 1.0.5 =
This version fixes a major bug and all users should upgrade immediately.

== Screenshots ==
1. The administration screen is the landing page for all the functionality of the plugin.
2. Form Designer - custom form designer built right into the plugin
3. View campaign details on the Campaigns screen
4. Edit Contacts inside the plugin
5. Update list names
6. Embed Events inline and view EventSpot event details

== Frequently Asked Questions ==

= The plugin Requires PHP 5.3 =
__Version 3.x changes requirements for your server. If you upgrade and the upgrade doesn't work for you, you can downgrade to the previous version of the plugin.__

Why? Because [Constant Contact's official code](https://github.com/constantcontact/php-sdk) requires PHP 5.3. 

Ask your host about upgrading your server to 5.3. If they say no, chances are you should find a new host; 5.3 has long been available.

= Do I need a Constant Contact account for this plugin? =
This plugin requires a <a href="http://wordpress.constantcontact.com/index.jsp" rel="nofollow" title="Sign up for Constant Contact">Constant Contact account</a>.

Constant Contact is a great email marketing company -- their rates are determined by the number of contacts in your list, not how many emails you send. This means you can send unlimited emails per month for one fixed rate! <a href="http://wordpress.constantcontact.com/features/signup.jsp" title="Try out Constant Contact today" rel="nofollow">Give it a test run</a>.

= Is there shortcode support? =

### Form Shortcode ###

There is shortcode support for the Form Designer forms: `[constantcontactapi]` with the following options:

<pre>
'formid' => 0, // REQUIRED
'before' => null,
'after' => null,
'redirect_url' => false,
'lists' => array(),
'title' => '',
'exclude_lists' => array(),
'description' => '',
'show_list_selection' => false,
'list_selection_title' => 'Add me to these lists:',
'list_selection_format' => 'checkbox'
</pre>

So to add a form, you would add the following in your content: `[constantcontactapi formid="3"]`

### Event Shortcode ###

To show event details, you can use the `[eventspot]` shortcode with the following options:

<pre>
'id' => null, // Show a specific event; enter Event ID (found on the Events page) to use
'limit' => 3, // Number of events to show by default
'showdescription' => true, // Show event Description
'datetime' => true, // Show event Date & Time
'location' => false, // Show event Location
'map' => false,  // Show map link for Location (if Location is shown)
'calendar' => false, // Show "Add to Calendar" link
'directtoregistration' => false, // Link directly to registration page, rather than event homepage
'newwindow' => false, // Open event links in a new window
'style' => true // Use plugin styles. Disable if you want to use your own styles (CSS)
</pre>

<strong>Sample Event Shortcodes</strong>

* To show event details for 5 events using the default settings, you would use `[eventspot limit=3]`
* To show event details for a single event with the id of `abc123` and also show the location details and map link, you would use: `[eventspot id="abc123" location=true map=true]`
* To use your own CSS file, you would use `[eventspot style=false]`


= How do I use the new `apply_filters()` functionality? (Added 1.1) =
If you want to change some code in the widget, you can use the WordPress `add_filter()` function to achieve this.

You can add code to your theme's `functions.php` file that will modify the widget output. Here's an example:
<pre>
function my_example_function($widget) {
	// The $widget variable is the output of the widget
	// This will replace 'this word' with 'that word' in the widget output.
	$widget = str_replace('this word', 'that word', $widget);
	// Make sure to return the $widget variable, or it won't work!
	return $widget;
}
add_filter('constant_contact_form', 'my_example_function');
</pre>

You can modify the widget output by hooking into any of the filters below in a similar manner.

To modify the Events widget output, start with the following code, again in your theme's `functions.php` file:

<pre>
add_filter('cc_event_output_single', 'cc_event_output_single', 1, 2);

function cc_event_output_single($output, $pieces = array('start'=> '','title'=>'','description'=>'','date'=>'','calendar'=>'','location' => '', 'end'=>'')) {
	// The pieces of each event are stored in the $pieces array
	// So you can modify them and cut and paste in what order you
	// want the pieces to display
	return $pieces['start'].'<dt>Description</dt>'.$pieces['description'].$pieces['date'].$pieces['end'];
}
</pre>

<strong>Some example filters:</strong>

* Entire form output: `constant_contact_form`
* Successful submission message: `constant_contact_form_success`
* Form description text: `constant_contact_form_description` (after it has been modified by `wpautop()`)
* Error message: `constant_contact_form_errors`
* Submit button: `constant_contact_form_submit` (includes entire `input` string)

= My email campaign click data isn't being tracked in Constant Analytics =
Constant Contact does not have built-in Google Analytics "tagging" that would track the click data. When you create links in your Constant Contact campaigns, <strong><a href="http://www.google.com/support/analytics/bin/answer.py?answer=55578" rel="nofollow">use the Google URL Builder</a></strong> to add tags to your links. <strong>Make sure to set the Campaign Medium to `email`!</strong>

When you do that, email click stats will be segmented for you in the Site Traffic box.

= How do I use the Form Designer? =

### Using the new Form Designer
1. Install this plugin.
2. Activate the Constant Contact API: Form Designer plugin
3. Configure the settings on the form designer by updating the settings in the boxes on the left.
4. Next to "Form Name" where it says "Enter form name here," enter your form name.
5. Once you have configured and named your form, click Save Form.
6. In the Appearance menu of the administration, click the Widgets link.
7. Drag the widget named "Constant Contact Form Designer" into the sidebar.
8. Configure the settings shown, then click the "Save" button at the bottom of the widget form.
9. You will see the signup widget you created on your website!
10. To edit the form, return the the Form Designer page (from Step 3) and click on the form tab with the name of the form you would like to edit. Edit the form, then click Update Form. The form will show as updated on your website.

= Form Designer isn't showing up or working =
Form Designer needs to be activated separately from the main plugin (see "How do I use the Form Designer?" above). Once you activate it, if it's still not working, it's likely a server issue.

The problem is that your web server may think that Form Designer is an unwelcome script. In order to fix this, you should <strong>contact your web host and request that they "whitelist your domain for ModSecurity."</strong>.

<a href="http://www.hostgator.com" rel="nofollow">HostGator</a> reps said whitelisting your own domain is <strong>not an issue that affects website security</strong>.

= My gradients aren't working on the Form Designer! =
This plugin uses <a href="http://planetozh.com/blog/my-projects/images-php-gd-gradient-fill/">Ozh's gradient script</a>. Please refer to that page.

= What is the plugin license? =
Good news, this plugin is free for everyone! The plugin is [licensed under the GPL](http://www.gnu.org/licenses/gpl-3.0.txt "View the GPL License").


= Registration form =
To make the checkbox appear after the "Signup Title", add the following to your theme's `functions.php` file:

`
add_filter( 'constant_contact_register_checkbox_before', '__return_false' );
`