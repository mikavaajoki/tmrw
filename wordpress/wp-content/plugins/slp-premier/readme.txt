=== Store Locator Plus™ - Premier ===
Plugin Name:  Store Locator Plus™ - Premier
Contributors: charlestonsw
Donate link: https://wordpress.storelocatorplus.com/product/premier-subscription/
Tags: Store Locator Plus™, premier
Required PHP: 5.3
Requires at least: 4.4
Tested up to: 4.9.7
Stable tag: 4.9.17

A plugin that brings new features and services for active Premier Subscription members.

== Description ==

Extend Store Locator Plus™ with new features and services for Premier Subscription members.

Upgrades to the plugin, which often contains new features, requires an active subscription.

All features from 4.7 on require an active Premier subscription or the features are disabled.

== Changelog ==

= 4.9.17 =

* IMPROVED Added the ability to turn the full screen control on/off on the map.
* IMPROVED Added the ability to turn the zoom control on/off on the map.
* IMPROVED Added ability to change invalid address message.
* IMPROVED the location output modifiers for under the map now are reflected in Power directory landing pages.
* PATCHED WooCommerce add new order item parameter error from WooCommerce 3.X and up.
* CHANGED Requires SLP 4.9.17

= 4.9.14 =

* PATCHED activation of territory bounds 

= 4.9.11 =

* IMPROVED added locations loading indicator settings for the front end.
* IMPROVED browser memory and performance when cluster map markers are not enabled.

= 4.9.8 =

* PATCHED JavaScript errors if cluster options not initialized.
* PATCHED Search when map moved to stop research looping.

= 4.9.7 =

* CHANGED search when map moves should not be on by default

= 4.9.6 =

* PATCHED search when map moves being hyper-active

= 4.9.3 =

* IMPROVED added a new search when maps moves feature, it is on by default.
* PATCHED fix the cluster markers, reset on subsequent location searches.
* CHANGE requires SLP 4.9.3

= 4.9.2 =

* PATCHED crash when using URL Controls if the subscription is no longer valid (expired or not entered on General tab).
* PATCHED [button bar not filtering locations](https://docs.storelocatorplus.com/issues/2017/12/09/premier-category-button-bar-not-filtering-categories/).
* IMPROVED the following settings are now Smart Options and can be set via the Plugin Style Gallery:
** Settings | Map | Appearance | Bubble Footnote
** Settings | Map | Appearance | Clickable Icons
** Settings | Map | Appearance | Hide Street View
** Settings | Results | Appearance | Do Not Wrap Results In Div
** Settings | Results | Appearance | Enable Pagination
** Settings | Search | Labels | Search Box Title
** Settings | Results | Appearance | Results Header
** Settings | Results | Labels | Results Header 1/2/3/4
** Settings | Search | Labels | Search Box Title

= 4.9.1 =

* PATCHED updated to work with PHP 5.3 (minimum supported SLP version).
* PATCHED to address an issue with WooCommerce and non-English sites.
* IMPROVED the button bar category selector honors the Settings | Search | Category Selector | Hide Empty Categories setting.
* NOTE The Hide Empty Categories on the button bar selector will show a category on the button bar even in the Pages module from Power is inactive.
* NOTE Unlike other category selectors, the button bar will show a category if Pages IS being used and all locations in the category has pages set to draft mode.

= 4.9 =

* NEW scroll map into view on results functionality.   Add class="scroll_to_map" on any results layout #results_wrapper_<id> element.
* NEW Category Button Bar selector option.
* IMPROVED [slp_search_element dropdown="category"] support for output of Button Bar, Horizontal and Vertical Checkboxes, and Single Parents without a preceding label.
* IMPROVED Update help text for Results Header.
* PATCHED Scheduled Geocoding exeuction.
* PATCHED Scheduled Initial Distance calculation execution.
* PATCH White Screen Of Death (WSOS) on sites using PHP < 5.4, but why are they?  PHP 5.3 was end-of-life August 2014.
