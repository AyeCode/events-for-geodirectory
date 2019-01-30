=== GeoDirectory Events ===
Contributors: stiofansisland, paoltaia
Donate link: https://wpgeodirectory.com/
Tags: geodirectory, geodirectory events, event, event manager
Requires at least: 4.9
Tested up to: 5.0
Stable tag: 2.0.0.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

GeoDirectory Events allows you to extend your GeoDirectory with a versatile event manager.

== Description ==

The GeoDirectory Events add-on is a compelling way to feature events in your directory, using Custom Post Types (CPT). The add-on creates the CPT Events, by adding time and date to a standard listing. Users can now choose to list a place or an event.

Do you host the same event every week or every month? You can set events as recurring daily, weekly, monthly, annual events and even pick custom dates, to save entering a separate one-off listing each time.

It's easy to sort events by selecting "upcoming," "today," "past" or "all" and display custom events lists, grids and a simple events calendar anywhere using widgets. By default, events are ordered with the upcoming event first.

== Installation ==

1. Upload 'geodir_event_manager' directory to the '/wp-content/plugins/' directory
2. Activate the plugin "GeoDirectory Events" through the 'Plugins' menu in WordPress
3. Go to WordPress Admin -> Events -> Settings and customize behaviour as needed

== Changelog ==

= 2.0.0.6 =
* Events query conflicts with advance ads plugin query - FIXED
* Event detail page not generates startDate, endDate structured data - FIXED 

= 2.0.0.5 =
* [gd_post_meta] always shows icon & label for event_dates - FIXED
* Some timezone shows date one day ahead in calendar selected dates for custom recurring event - FIXED

= 2.0.0.4 =
* Changes for franchise manager addon - ADDED

= 2.0.0.3 =
* Some plugins WP Query conflicts with event calendar query - FIXED
* Added event_past class to calender widget for past dated - ADDED

= 2.0.0.2-rc =
* Adding new date to custom recurring event resets the start and end hours - FIXED

= 2.0.0.1-rc =
* Install script should only run if not upgrading from v1 - FIXED

= 2.0.0.0-beta =
* First beta release - INFO
