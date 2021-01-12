=== Events Calendar for GeoDirectory ===
Contributors: stiofansisland, paoltaia, ayecode
Tags: events, calendar, event, schedule, organizer, geodirectory, event listings, events directory, event manager
Donate link: https://wpgeodirectory.com
Requires at least: 4.9
Tested up to: 5.6
Requires PHP: 5.6
Stable tag: 2.1.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Events Calendar for GeoDirectory add-on allows to extend your GeoDirectory powered website with a versatile event manager.

== Description ==

The Events Calendar for GeoDirectory add-on is a compelling way to feature events in your directory, using Custom Post Types (CPT). The add-on creates the CPT Events, by adding time and date to a standard listing. Users can now choose to list a place or an event.

= Recurring Events Included =

Do you host the same event every week or every month? You can set events as recurring daily, weekly, monthly, annual events and even pick custom dates, to save entering a separate one-off listing each time.

It's easy to sort events by selecting "upcoming," "today," "past" or "all" and display custom events lists, grids and a simple events calendar anywhere using widgets. By default, events are ordered with the upcoming event first.

= Events Locator =

Events can be easily displayed on a map, allowing user to locate your events with ease. Easily show distance from them and directions.

= Premium add-ons =

* [SEO Location Manager](https://wpgeodirectory.com/downloads/location-manager/ "Allows to create a global directory") - Create a global Events Directory with unique pages for Countries, Regions, Cities, and Neighbourhoods. Also create Virtual Events without a physical address.
* [Pricing Manager](https://wpgeodirectory.com/downloads/pricing-manager/ "Allows to monetize your Events") - Set prices for your Events. Enable/disable features per price. It uses our free [Invoicing Plugin](https://wordpress.org/plugins/invoicing/ "Invoicing plugin for WordPress") to manage payments, taxes, and invoices.
* [Custom Post Types](https://wpgeodirectory.com/downloads/custom-post-types/ "Allows to extend your directory categorization") - Create unlimited events custom post type, each with its own custom fields, prices and more.
* [MultiRatings and Reviews](https://wpgeodirectory.com/downloads/multiratings-and-reviews/ "Allows you to extend your rating and reviews categorization") - Extend the review system allowing multiple rating categories (e.g., service, quality, price), add images to reviews and other cool features.
* [Advance search filters](https://wpgeodirectory.com/downloads/advanced-search-filters/ "Allows you to extended search with custom filters") - Turns any Events custom field into an advance filter of the search widget. Adds smart autocompletes, geolocation, and much more. Search Events by date.
* [Buddypress Integration](https://wpgeodirectory.com/downloads/buddypress-integration/ "integrates Buddypress with The events Calendar for GeoDirectory") - Smoothly integrates GeoDirectory Events with Buddypress.
* [Claim Listing Manager](https://wpgeodirectory.com/downloads/claim-manager/ "Allows users to claim their Events") - Allow events owners to fine-tune their event listings, add images, link to places (venues) and show an 'owner-verified' badge on the listing. Now with force upgrade/paid option.
* [Marker Cluster](https://wpgeodirectory.com/downloads/marker-cluster/ "To avoid overcrowded maps") - Avoid cluttered maps by using numbered markers at high zoom levels. Now with super fast server-side clustering!
* [Duplicate alert](https://wpgeodirectory.com/downloads/ajax-duplicate-alert/ "Events  already exists?") - Alert users when they add an event with the same title (or other details) as another event.
* [Custom Map Styles](https://wpgeodirectory.com/downloads/custom-google-maps/ "Customize your maps look and feel") - Modify the look and feel of all Maps widgets via an intuitive user interface, with color pickers and simple-to-use options.
* [Social Importer](https://wpgeodirectory.com/downloads/social-importer/ "Import 1 Event at a time from Facebook!") -  Import events from Facebook. One listing at a time, no bulk scraping.
* [GD reCAPTCHA](https://wpgeodirectory.com/downloads/gd-recaptcha/ "Stop spammers!") - Banish spam by adding the No CAPTCHA reCAPTCHA widget to any GeoDirectory form.
* [Franchise Manager]( https://wpgeodirectory.com/downloads/franchise-manager/ "Franchise Manager") - Allows users to submit Events that span into multiple locations.
* [List Manager]( https://wpgeodirectory.com/downloads/list-manager/ "List Manager") - Allows users to create their lists of events and make them public to other users.
* [WP All Import]( https://wpgeodirectory.com/downloads/wp-all-import/ "WP All Import") - Use the power of WP All Import to import your listings from anywhere with this add-on that integrates Wp All Import with The Events Calendar for GeoDirectory
* [Embeddable Ratings Badge]( https://wpgeodirectory.com/downloads/embeddable-ratings-badge/ "Embeddable Ratings Badge") - Let users embed their Events info with current ratings on their site, styled the way they want.
* [Compare Listings]( https://wpgeodirectory.com/downloads/compare-listings/ "Compare Listings") - Let your users compare Events side by side and compare vital info about the Events.

= Go Pro - Become a member! =

Get your hands on all the Events Calendar for GeoDirectory premium add-ons and themes. Sign up at [wpgeodirectory.com](https://wpgeodirectory.com/downloads/membership/ "Get GeoDirectory membership.").


== Installation ==

1. Upload 'events-for-geodirectory' directory to the '/wp-content/plugins/' directory
2. Activate the plugin "Events for GeoDirectory" through the 'Plugins' menu in WordPress
3. Go to WordPress Admin -> Events -> Settings and customize behaviour as needed

== Changelog ==

= 2.1.1.0 =
* [gd_post_meta] Fix formatting issue in event dates shortcode - ADDED
* Show month & year dropdown in event dates datepicker - CHANGED
* Plugin name changed to "Events for GeoDirectory" - CHANGED

= 2.1.0.2 =
* [gd_post_badge] now supports past, ongoing, upcoming conditions for events - ADDED

= 2.1.0.1 =
* Change Jquery doc ready to pure JS doc ready so jQuery can be loaded without render blocking  - CHANGED
* Shows incorrect start time & end time with bootstrap style - FIXED

= 2.1.0.0 =
* Changes for AyeCode UI compatibility - CHANGED
* Event cal widget will lazy load now - CHANGED
* No way to show event description set in field setting - FIXED

= 2.0.1.1 =
* Web accessibility changes in search by event dates - CHANGED
* Set performer & organizer in schema from fields if exists - CHANGED

= 2.0.1.0 =
* Screen keyboard on iPhone 7 prevents working with the calendar - FIXED
* Set event end to max no. of repeat if repeat date is empty - FIXED
* Add hook for date and time separator - ADDED
* Event post type category add/edit page shows wrong event schema options - FIXED
* Sometime event calendar fails to load data on large directory - FIXED
* [gd_post_meta] widget/shortcode key added for start date, end date, start time, end time - ADDED
* Hackathon added as new event schema option - ADDED

= 2.0.0.18 =
* Spelling mistake on online only event schema - FIXED
* Set EventScheduled to active in schema even if no status is set - CHANGED

= 2.0.0.17 =
* Allow to show event date raw value with gd_post_meta - CHANGED
* Changes for Schema markup for disrupted events - ADDED
* New pre defined field for setting event disruption status - ADDED

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
