=== Store Locator Plus™ - Power ===
Plugin Name:  Store Locator Plus™ - Power
Contributors: charlestonsw
Donate link: https://www.storelocatorplus.com/
Tags: bulk import, seo, data management
Required PHP: 5.3
Requires at least: 4.4
Tested up to: 4.9.8
Stable tag: 4.9.20

Adds power user features include location import, categorization, and SEO pages to Store Locator Plus™.

== Description ==

Adds the features needed by power users such as bulk import of locations, SEO management, and advanced interactive map elements.

This is the newest add-on pack that rolls up the features of the legacy Pro Pack, Tagalong, Pages, Contact Extender, and Directory Builder add-on packs in one convenient package.

== Changelog ==

= 4.9.20 =

* PATCHED location sensor

= 4.9.19 =

* PATCHED allow the location Pages, Delete Permanently bulk action to clean up mis-linked pages (caused from side-loading imports)
* PATCHED zoom map

= 4.9.17 =

* IMPROVED the location output modifiers for under the map now are reflected in directory landing pages.
* IMPROVED the [slp_directory] landing pages now wrap output in standard results layout divs to take on default style gallery styling attributes.
* IMPROVED Store Pages templates can now use the [slp_location...] shortcode to output location data similar to the below-map results.
* IMPROVED [slp_location description] in Store Pages output can now render HTML that exists in the description field.
* IMPROVED [storepage map=location] shortcode uses default map styling and settings.
* PATCHED Scheduled Imports
* PATCHED to allow two [slp_pages...] shortcodes with different attributes to appear on the same page.
* PATCHED turning off log import messages.  Default is not off for new installs.
* CHANGED requires SLP 4.9.17

= 4.9.15 =

* IMPROVED allow [slp_pages...] to provide custom page templates based on the content between tags: [slp_pages ...]the page template here[/slp_pages]
* IMPROVED add css class attributes to [slp_pages type="hyperlink"]
* IMPROVED allow the pages_directory_wrapper_css_class [slp_pages] attribute to be used on style="summary" and style="bullet" listings.
* IMPROVED [store_page ...] now supports a tag="..." attribute which will wrap the output in the specified HTML tag if there is output.
* IMPROVED [store_page ...] now supports a class="..." to augment the [store_page ... tag="H1"] HTML tag wrapper with a class.
* PATCHED duplicate bulk action drop down entries.
* PATCHED enable reports

= 4.9.14 =

* IMPROVED updated to match the new SLP add/edit locations Vue format.
* IMPROVED added a [storepage post no_map="1"] option to skip rendering the map for special use cases.
* IMPROVED added a [slp_pages nomap="1"] option to skip rendering the pages list for special use cases to avoid map overhead on lists of pages.
* IMPROVED added options to set page directory HTML element classes on Pages List items and the item wrapper.  Useful for themes with pre-defined 12-point grid layouts.
* IMPROVED allow slp_pages to take on Smart Options as attributes like [slp_pages style="full" no_map=”1” pages_directory_entry_css_class="col-xs-12"] as well as pages_directory_wrapper_css_class
* PATCHED creates store pages when locations are added via the REST handler
* PATCHED checking off the proper categories when editing a location
* PATCHED Do not add the category drop down search element shortcode when hide_search_form='true' is passed in slplus.
* PATCHED JS error of slplus.options not defined when rendering slp_pages shortcodes.
* CHANGED requires SLP 4.9.14

= 4.9.11 =

* IMPROVED the [slp_pages] shortcode now allows locations to be listed by whether or not they are marked as featured.
* IMPROVED the [storepage type="hyperlink"] now supports a title="Text" attribute to show the user different link text.
* PATCHED fixes the create page icon on the list locations not updating page templates if the template had been modified.
* CHANGED requires SLP 4.9.11

= 4.9.9 =

* PATCHED invalid category terms when the taxonomy structure has failed.

= 4.9.8 =

* PATCHED AJAX failure on location queries.
* IMPROVED Minor performance update on delete all locations when categories in use.  WP Core is the main source of performance issues.

= 4.9.7 =

* IMPROVED add an option to remove old report records

= 4.9.4 =

* PATCHED multisite location import
* PATCHED remove slp_addon codes from results layout for directory landing pages.

= 4.9.3 =

* IMPROVED location sensor support, non-https sites will now fall back to working as if location sensor was disabled.
* IMPROVED location imports can use the category_slug column only if the categories already exist with those slugs.
* PATCHED fgetcsv escape character is a single character.  Fix the PHP warning.
* CHANGED the custom categories tab is gone the tab now links to the standard WordPress Stores taxonomy tab.

= 4.9.2 =

* IMPROVED better detection of CSV file format errors especially missing newline (\n) characters.  Files ending with only return (\r) characters cannot be processed by PHP CSV functions.
* IMPROVED report the number of fields (columns) in a CSV file under the WordPress Media library review of an uploaded CSV.
* IMPROVED eliminated added, updated, and not updated messages on CSV imports to reduce message array size.  Only report notable events.
* PATCHED Import Locations corruption when the server PHP timeout stops the import mid-file and the background restarts.
* PATCHED Locations | Import | Remote File Retrieval saving of URL and schedule parameters now auto-save when changed.
* PATCHED fixed missing array index warnings if an import was deleted from the WP Media Library while it was still being processed.
* CHANGED Do not use the 'Identifier' column as a special location matching field if General > Data > Add On Data Extensions > Enabled Contact Fields is not enabled.
* CHANGED CSV Imports no longer honor \ as an escape character for CSV delimiters. Only (RFC 4180)[https://tools.ietf.org/html/rfc4180] "" is allowed.


= 4.9.1 =

* PATCHED registration of Store Pages under WP 4.9
* PATCHED Address 2 field being mapped to Address field in import processor.
* PATCHED array references to allow SLP to work with PHP version < 5.4 including PHP 5.3
* IMPROVED the location import in-progress notifications showing current location import and geocoding state.
* Extend SLP REST API to list active running import cron jobs:  /wp-json/store-locator-plus/v2/imports/
* Extend SLP REST API to list active running geocoding cron jobs:  /wp-json/store-locator-plus/v2/geocoding/

= 4.9 =

* IMPROVED [slp_search_element dropdown="category"] support for output of Premier Button Bar, Horizontal and Vertical Checkboxes, and Single Parents without a preceding label.
* IMPROVED Location import now puts the location import files in the WordPress Media library for easier management after uploading and processing.
* IMPROVED The icon and map marker selector for categories is now using the updated selector with the ability to use the WordPress Media Library.
* PATCHED Icon and Map Marker selector for categories is working again.
* PATCHED Update to fix Pages single-page creation action and make it an instant (no page-reload needed) action.
* PATCH White Screen Of Death (WSOS) on sites using PHP < 5.4, but why are they?  PHP 5.3 was end-of-life August 2014.
* Requires SLP 4.8.8 or higher
* Tested with WP 4.9

= 4.8.6 =

* Fix category processing.
* Patched all SQL data references for WP 4.8.2 security updates affecting data queries.

= 4.8.4 =

* Update sv_SE language files.
* Fix the category manager icon/map marker selector.
* Updated to work with the Premier Category Selection Checkbox option.

= 4.8.3 =

* Fix error with allow tag in URL.

= 4.8.2 =

* Skip CSV data lines that are completely empty.
* The header "Name" can be used in place of 'Store' or 'sl_store' on the CSV header line.
* Fix the tags not saving when editing a location.
* Fix options availability on individual sites on multisite installs.

= 4.8 =

* IMPROVED Minify some admin JavaScript.
* PATCH the import Duplicates Handling setting (requires SLP 4.8)
* PATCH White Screen Of Death (WSOS) on sites using PHP < 5.4, but why are they?  PHP 5.3 was end-of-life August 2014.


= 4.7.11 =

* Update sv_SE translations.
* Updated to work with SLP 4.7.10

= 4.7.10 =

* Fix issue with not properly attaching categories to a location.
* Requires SLP 4.7.10+

= 4.7.9 =

* Interface updates to match SLP 4.7.9
* Add a new [slp_directory locator_data="..."] parameter to override the locator page parameter passing.
* [slp_directory locator_data="sl_id"] will pass along the first location_id , perfect for using with Premier's new URL Control features.
* Add a new [slp_directory only_with_category="..."] parameter.
* Fix a bug in [slp_directory] class names on list output.

= 4.7.8 =

* Add [slp_category legend] shortcode (same as [tagalong legend] shortcode).
* Update TableSorter JavaScript to work with latest jQuery and get report graphs and export feature going.
* Fix the Export Reports processor both for downloading and selecting all records.
* Requires SLP 4.7.8

= 4.7.6 =

Update to work with SLP 4.7.6.

= 4.7.3 =

* Tested with WP 4.7.1
* Updated for better compatibility with SLP 4.7.3

= 4.7.1 =

Enhancements

* Slightly faster scheduled imports.
* Better messaging for Premier Scheduled Geocoding tasks.

Fixes

* Bulk geocode all uncoded has been fixed and now geocodes ALL records not every-other one.

Changes

* Schedule messages moved to General / Schedule tab.
* Log import messages checkbox and Log schedule messages checkbox are on the General tab so Premier can play too.
