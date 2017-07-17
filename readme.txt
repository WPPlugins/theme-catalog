=== Theme Catalog ===
Contributors: trex005
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=4JLY4397X5CWG&lc=US&item_name=Tapy&no_note=0&cn=Message&no_shipping=1&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted
Tags: theme, themes, picker, chooser, selector, catalog, menu, menus, widget, widgets
Requires at least: 4.0
Tested up to: 4.2
Stable tag: 0.02
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Attractive front-end theme switcher/preview allowing demo of all themes, with menu and widget handling.

== Description ==
Attractive front-end theme switcher/preview allowing demo of all themes, with menu and widget handling.

[Demo Site](http://themes.tapyweb.com/theme-catalog/)

= Usage =
Add the shortcode [theme_catalog_select] to any page or post to display an attractive list of themes available to preview

= Shortcode Attributes =
* **errors** (true,false,null) *default:false* - Display only themes with errors. true to only show errors, false to only show non errors, null to show both [Read More](https://codex.wordpress.org/Function_Reference/wp_get_themes)
* **allowed** (true,false,'site','network',null) *default:true* - Display only allowed themes [Read More](https://codex.wordpress.org/Function_Reference/wp_get_themes)
* **screenshots** (true, false, null) *default:true* - Display only themes that have screenshots. true to only show themes with screenshots, false to only show themes without screenshots, null to show both
* **lazyload** (true,false,integer) *default:20* - whether to use lazyload. true to always use lazyload, false to never use lazyload, integer to use lazyload if at least specified amount of results are found.
* **display_screenshot** (true,false) *default:true* - Display screenshots
* **display_name** (true,false) *default:true* - Display names
* **display_author** (true,false) *default:true* - Display authors
* **display_version** (true,false) *default:true* - Display versions
* **display_description** (true,false) *default:true* - Display description

= Menus =
Menus can be created/mapped on a per theme basis and global menus can be created which will be used in the event that the menus are not mapped for the selected theme.

* **Individual Theme Menus** can be set up.  To do so :
   1. Turn on "Apply in admin" under `Settings -> Theme Catalog`
   2. Use the page you have the selector on to switch to the desired theme.
   3. Go to `Appearance -> Menus` and set up menus as you wish
   4. Repeat steps 2 & 4 for all the themes you want to individually map.
   5. Turn off "Apply in admin" under `Settings -> Theme Catalog`
* **Global Theme Menus** can be created by creating menus named "theme_catalog_menu_1" "theme_catalog_menu_2" etc.  Menus will be populated into loaded theme in the order they are encountered.

= Widgets =
Widgets can only be handled on a per theme basis.  To do so :

   1. Turn on "Apply in admin" under `Settings -> Theme Catalog`
   2. Use the page you have the selector on to switch to the desired theme.
   3. Go to `Appearance -> Widgets` and set up widgets as you wish
   4. Repeat steps 2 & 4 for all the themes you want to individually map.
   5. Turn off "Apply in admin" under `Settings -> Theme Catalog`

= Issues =
iThemes Builder themes and Artisteer themes (and I'm sure there are others) bypass WordPress' wp_nav_menu function, so can not use global menus.  You must use individual menu mapping for these.

== Installation ==
= The Easy Way =
1. Install and Activate from `Plugins->Add New` in Wordpress
1. Go to Step 4 in "Manually" section
= Manually =
1. Upload `theme-catalog.zip` to the `/wp-content/plugins/` directory
1. Unzip `theme-catalog.zip`
1. Activate the plugin through the 'Plugins' menu in WordPress
1. *Optionally* set up menus for each theme, and/or global menus (more details under description)
1. *Optionally* set up widgets for each theme
1. Place `[theme_catalog_select]` in your page or post

== Frequently Asked Questions ==
= My theme screenshots are not loading! =
Did you specify `screenshots='false'` or `display_screenshot='false'`?

If not, there is a good chance your theme is not properly calling wp_footer().  To solve this problem, you need to either :

* Turn off lazy loading by adding `lazyload='false'` to your shortcode
* load `lazyload` in your site's header by going to `Settings -> Theme Catalog` and change "Lazyload in header" to "yes".
= What is "plugin prefix" in the settings? =
This allows you to change the prefix used in the shortcode, url parameters, session variables and css classes. Changing it would reset all of your visitors back to your default theme, but would also require you to edit your shortcode. Unless you are having problems you should never need to change this.

== Screenshots ==
1. A selection of themes the user can change to.

== Changelog ==
= 0.02 =
* Fixed settings menu not showing up for non multisite admins
* Use proper PHP open tag for compatability on more servers.
= 0.01 =
Initial release

== Upgrade Notice ==
= 0.02 =
Critical bug fixes
= 0.01 =
Initial release

== TO DO ==
* Add 'include' & 'exclude' attributes
* Build a tool for bulk mapping theme menus.
* Allow sidebars (widget areas) to be created and mapped on a per theme basis or globally