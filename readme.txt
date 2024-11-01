=== Site Info ===
Contributors: johnalarcon
Tags: site info, site meta, server info, server, version, php, mysql, settings
Requires at least: 4.7
Tested up to: 5.0
Requires PHP: 5.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Quickly see your site's version numbers, database info, installed plugins and themes, paths and URLs, ports, protocols, and other info. Built as a POC for a local Meetup.

== Description ==
The **Site Info** plugin helps you discover key information about your site in a single, useful display. This can save you a lot of clicking and logins when you need to gather this information bit-by-bit.

WordPress' built-in icons are used throughout to bring your attention to any problems found with your site or server. You will find that things like an outdated WordPress version, an insecure PHP version, non-use of HTTPS, etc., are marked prominently.

**PHP, database, and WordPress versions?** *No problem!*

**Relative and absolute paths?** *There you go!*

**Database server, character set, or table prefix?** *No sweat!*  

**Installed plugins and themes?** *Of course!* 

**Server OS, cURL version, and supported SSL protocol?** *Gotcha covered!*

== Installation ==
* Navigate to your plugins admin page.
* Search for "site info" and download the plugin.
* Install using the plugin admin interface.

== Screenshots ==
screenshot-1.png
screenshot-2.png

== Frequently Asked Questions ==
**Does it work with Gutenberg?**
Yes. Because the plugin does not have any editor functionality, it is unaffected by Gutenberg.

**Does it work with ClassicPress?**
Yes.

**Where is my site info displayed?**
In your WordPress dashboard, hover the **Tools** menu and you will find a new item named **Site Info**.

**Which users can see the site information?**
Users with *manage_options* capability can access the plugin. These users are typically in admin or super-admin roles.

**Is my database username and password exposed onscreen?**
No. The only database-related items shown are: database host, table prefix, character set, collation, and type of database.

**Can you add something else for me?**
This plugin was written specifically for a presentation I gave at my local WordPress Meetup group. I've made it available because it can be a handy resource. However, further development is not planned, but if you can make a compelling argument for including something more, I'll certainly consider it.

**I found a bug!**
Let me know on the support forum and I'll get it fixed. Please note that if you're requesting a "missing" feature, it won't necessarily be considered a bug.

== Changelog ==
**1.2.1**
Fix: Absolute paths were referred to as relative paths; incorrect text.

**1.2.0**
Add conditional to check whether installed on WordPress or ClassicPress; update onscreen titles accordingly.

**1.1.0**
Fix Server Error 406 that occurred on some hosts due to including a local file via HTTP URL instead of relative path.