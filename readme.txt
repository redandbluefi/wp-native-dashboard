=== WP Native Dashboard ===
Contributors: codestyling
Tags: wordpress, dashboard, multi-lingual, languages, backend, localization, plugin
Requires at least: 2.7
Tested up to: 2.9-rare
Stable tag: 1.1.0

Enables selection of administration language either by logon, dashboard quick switcher or user profile setting.

== Description ==

This plugin enables the selection of your prefered language the dashboard (and whole administration) will be shown during your work.
Several options can be enabled and also combinations out of:

1. logon screen extension - user can specify his/her prefered language during logon 
1. dashboard quick switcher extension - user can easily switch language at every admin page
1. user profile setting - each user can define at profile his/her prefered language

The plugin also includes a repository scan on demand (svn.automattic.com) for available language file downloads.
You can download the required files into your installation and immediately use them at admin pages.
The new administration page is restricted to administrators only, the profile setting also work for subscriber.

= Download and File Management = 

Starting with version 1.1.0 of this plugin it uses now the WordPress build-in file management from core. If the plugin detects, that you are not permitted to write directly to disk, it uses the FTP user credentials for download and remove of language files.

= WordPress / WPMU and BuddyPress =

If you have a local WordPress community providing their own download repository for language files, please let me know, if you would like to get it integrated.
Because i didn't found an official language file repository for BuddyPress and WPMU, it currently only permits WordPress language file downloads.
If you have more specific informations about, please let me know, it's easy to integrate a new download section (also with detection the kind of WP).

= Requirements =

1. WordPress version 2.7 and later
1. PHP Interpreter version 4.4.2 or later

Please visit [the official website](http://www.code-styling.de/english/development/wordpress-plugin-wp-native-dashboard-en "WP Native Dashboard") for further details, documentation and the latest information on this plugin.

== Installation ==

1. Uncompress the download package
1. Upload folder including all files and sub directories to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Navigate to your Dashboard and enjoy status informations

== Changelog ==

= Version 1.1.0 = 
* full support of core file system usage (FTP if necessary)
* locale to language name mapping introduced (user friendly namings)
* beautyfied some UI states (alternate rows correction after download)

= Version 1.0.1 =
* Forcing jQuery usage even if a backend page (from another plugin eg.) doesn't make use of.
* providing official page link for supporting purpose.

= Version 1.0 =
* initial version


== Frequently Asked Questions ==
= History? =
Please visit [the official website](http://www.code-styling.de/english/development/wordpress-plugin-wp-native-dashboard-en "WP Native Dashboard") for further details, documentation and the latest information on this plugin.

= Where can I get more information? =
Please visit [the official website](http://www.code-styling.de/english/development/wordpress-plugin-wp-native-dashboard-en "WP Native Dashboard") for further details, documentation and the latest information on this plugin.


== Screenshots ==
1. dashboard quick switcher 
1. user profile setting extension
1. extended WordPress login screen
1. administration page
1. download scan process
1. full administration page
1. user credentials required for writing
