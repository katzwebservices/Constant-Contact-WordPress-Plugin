=== Constant Contact for Wordpress ===
Contributors: katzwebdesign, katzwebservices
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=zackkatz%40gmail%2ecom&item_name=Constant%20Contact%20API%20Plugin&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: mail, email, newsletter, Constant Contact, plugin, sidebar, widget, mailing list, API, email marketing, newsletters, form, forms, event, events, event marketing
Requires at least: 3.3
Tested up to: 4.0.1
Stable tag: 3.1.5
License: GPLv2 or later

Integrate Constant Contact into your website with this full-featured plugin.

== Description ==

> __This plugin requires a [Constant Contact account](http://wordpress.constantcontact.com).__  
> *Don't have an account?* Constant Contact offers a [free 60 day trial](http://wordpress.constantcontact.com/email-marketing/index.jsp "Sign up for a free Constant Contact trial"), so sign up and give this plugin a whirl!

#### Fully integrate Constant Contact with your WordPress website.

The Constant Contact for Wordpress plugin is the best email marketing plugin for WordPress: integrate your website seamlessly with your Constant Contact account.

You can place a signup checkbox or list selection on your register page or use the signup widget anywhere in your website sidebar or PHP templates.

### Event Marketing

The plugin features [Constant Contact Event Marketing](http://wordpress.constantcontact.com/event-marketing/index.jsp) functionality by allowing you to track events, registration, and registrants using the plugin. Simply navigate to Constant Contact > Events. Manage your events from inside WordPress!

### Built-in Form Designer

__The Form Designer__ is a form generation and design tool. The Form Designer allows users to generate unlimited number of unique forms and gives a wide variety of options that can be configured, including what fields to show in the signup form. There and tons of design options, including custom background images, border width, colors, fonts and much more.

### Constant Analytics: In-Depth Google Analytics
View your Google Analytics data in your dashboard with Constant Analytics. View traffic by source, geography, and popularity. See the impact of blog posts and email campaigns with the great graphing tools.

#### Plugin features:
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

#### Plugin Support
To obtain support please use this link to the [wordpress forums](http://wordpress.org/tags/constant-contact-api).

#### If you like the plugin...
If you use the plugin and find it useful please make sure to come back and vote so other users know it works.

#### Translate the plugin
Want to see this plugin in your language? [Join the translation team](https://www.transifex.com/projects/p/constant-contact-wordpress/) and help translate it!

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

= 3.1.5 on December 2 =
* Fixed: Catch `WP_Error` response from the REST client
* Fixed: Namespace the V1 API classes to fix Fatal Errors on activation when already having a plugin using the `OAuthSignatureMethod_HMAC_SHA1` class name
* Fixed: Form Styler does not stay turned off after saving
* Fixed: Issue where Akismet would return all submissions as spam
* Fixed: Improved error handling for DataValidation.com
* Added: `constant_contact_akismet_is_test` filter to tell Akismet not to train using test data
* Fixed: Added message for users who are `OPTOUT` status
* Fixed: Activity Log output now handles exceptions better
* Modified: Updated the phone number validation script
* Fixed: Hide PHP notices for SMTP Email Validation script

= 3.1.4 =
* Fixed: Original plugin file name restored to help with auto-upgrade issues
* Fixed: Form action URL incorrect for subdomain multisite installations. ([see ticket here](https://wordpress.org/support/topic/action-httpsitecom-within-multi-multi-site))
* Fixed: EventSpot widget PHP warnings
* Modified: New Form Designer forms have Submit field checked by default
* Fixed: Fatal error on plugin deactivation if PHP 5.3 isn't available
* Fixed: Form Designer Javascript Debug Mode turned off unless `SCRIPT_DEBUG` constant is defined and enabled
* Tweak: Don't modify order of Form Designer fields if no position is set
* Tweak: Improved text translations and fix textdomain issues

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

== Upgrade Notice ==

= 3.1.2 =
* __Major rewrite!__
	- Requires PHP 5.3
	- Uses different authentication method - password no longer stored on site
	- Completely re-written to be more stable and functional

= 2.4.1 =
* Fixed: issue with WordPress registration: the "Hidden Contact Lists" lists were not being properly added (the list IDs were incorrect), causing issues.
* Added: "Default Option Text" registration option, visible when using a Dropdown List "List Selection Format". Allows you to set the default text of the dropdown list.

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
This plugin requires a [Constant Contact account](http://wordpress.constantcontact.com/index.jsp).

Constant Contact is a great email marketing company -- their rates are determined by the number of contacts in your list, not how many emails you send. This means you can send unlimited emails per month for one fixed rate! [Give it a test run](http://wordpress.constantcontact.com/features/signup.jsp).

= Is there shortcode support? =

### Form Shortcode ###

There is shortcode support for the Form Designer forms: `[constantcontactapi]` with the following options:

```
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
```

So to add a form, you would add the following in your content: `[constantcontactapi formid="3"]`

### Event Shortcode ###

To show event details, you can use the `[eventspot]` shortcode with the following options:

```
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
```

__Sample Event Shortcodes__

* To show event details for 5 events using the default settings, you would use `[eventspot limit=3]`
* To show event details for a single event with the id of `abc123` and also show the location details and map link, you would use: `[eventspot id="abc123" location=true map=true]`
* To use your own CSS file, you would use `[eventspot style=false]`


= How do I use the new `apply_filters()` functionality? (Added 1.1) =
If you want to change some code in the widget, you can use the WordPress `add_filter()` function to achieve this.

You can add code to your theme's `functions.php` file that will modify the widget output. Here's an example:

```
function my_example_function($widget) {
	// The $widget variable is the output of the widget
	// This will replace 'this word' with 'that word' in the widget output.
	$widget = str_replace('this word', 'that word', $widget);
	// Make sure to return the $widget variable, or it won't work!
	return $widget;
}
add_filter('constant_contact_form', 'my_example_function');
```

You can modify the widget output by hooking into any of the filters below in a similar manner.

To modify the Events widget output, start with the following code, again in your theme's `functions.php` file:

```
add_filter('cc_event_output_single', 'cc_event_output_single', 1, 2);

function cc_event_output_single($output, $pieces = array('start'=> '','title'=>'','description'=>'','date'=>'','calendar'=>'','location' => '', 'end'=>'')) {
	// The pieces of each event are stored in the $pieces array
	// So you can modify them and cut and paste in what order you
	// want the pieces to display
	return $pieces['start'].'<dt>Description</dt>'.$pieces['description'].$pieces['date'].$pieces['end'];
}
```

__Some example filters:__

* Entire form output: `constant_contact_form`
* Successful submission message: `constant_contact_form_success`
* Form description text: `constant_contact_form_description` (after it has been modified by `wpautop()`)
* Error message: `constant_contact_form_errors`
* Submit button: `constant_contact_form_submit` (includes entire `input` string)

= My email campaign click data isn't being tracked in Constant Analytics =
Constant Contact does not have built-in Google Analytics "tagging" that would track the click data. When you create links in your Constant Contact campaigns, <strong><a href="http://www.google.com/support/analytics/bin/answer.py?answer=55578" rel="nofollow">use the Google URL Builder</a></strong> to add tags to your links. <strong>Make sure to set the Campaign Medium to `email`!</strong>

When you do that, email click stats will be segmented for you in the Site Traffic box.

= How do I use the Form Designer? =

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

= What is the plugin license? =
Good news, this plugin is free for everyone! The plugin is [licensed under the GPL](http://www.gnu.org/licenses/gpl-2.0.txt "View the GPL License").

= How do I test Akismet spam filtering? =

* Add a First Name field to the form you want to test 
* Log out of your site (Akismet takes into account if you're logged-in)
* Submit your form using the First Name field set to `viagra-test-123`
* That should always return false