=== GeoDirectory Events ===
Contributors: stiofansisland, paoltaia
Donate link: https://wpgeodirectory.com/
Tags: geodirectory, geodirectory events, event, event manager
Requires at least: 4.9
Tested up to: 5.3.2
Stable tag: 2.0.0.16
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

= 2.0.0.16 =
* Event calendar looses the location when redirected to search page - FIXED
* Show event date & time in RSS feed - ADDED
* Event range from/to not returning all relevant results - FIXED
* Listings widgets pagination shows wrong total number of events - FIXED
* Event categories shows wrong terms count when no location set - FIXED
* Single event option shows incorrect event date ordering - FIXED
* New "GD > Event Schedules" widget added to display event schedules - ADDED

= 2.0.0.15 =
* Linked posts widget has no option to filter event type - FIXED
* Unable to translate month names in custom event type calendar - FIXED
* Events calendar week start day setting not working - FIXED
* Events calendar shows non published events on calendar - FIXED
* Delete subsite removes data from main site on multisite network - FIXED

= 2.0.0.14 =
* Weekend starts on Saturday - CHANGED
* Remove pagination when an event filter is changed - FIXED

= 2.0.0.13 =
* Event title meta variables not working with Yoast SEO - FIXED

= 2.0.0.12 =
* Events spanning multiple days not shown for new sort time spans - FIXED

= 2.0.0.11 =
* Display event past schedules if event has no upcoming schedules - CHANGED
* More event filter options  - ADDED

= 2.0.0.10 =
* Map shows past events markers on map - FIXED
* Import events should supports m/d/y date format - CHANGED

= 2.0.0.9 =
* Allow to set recurring enabled by default for the event - CHANGED
* Class and highlighting added for today on event calender - ADDED
* Event dummy categories don't have new cat icon set - ADDED

= 2.0.0.8 =
* Edit form shows incorrect dates for the events created with v1 - FIXED

= 2.0.0.7 =
* Event categories shows wrong location term counts - FIXED
* Not able to translate "All %s" string - FIXED

= 2.0.0.6 =
* Events query conflicts with advance ads plugin query - FIXED
* Event detail page not generating startDate, endDate structured data - FIXED

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
