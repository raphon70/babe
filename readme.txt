=== BA Book Everything ===
Contributors: bookingalgorithms
Tags: booking, tours, cars, calendar, events
Requires at least: 6.0
Requires PHP: 7.4
Tested up to: 6.6.2
Stable tag: 1.6.24
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The really fast and powerful Booking engine with management system for theme/site developers to create any booking or rental sites (tours, cars, events, hotels, hostels, apartments etc.)

== Description ==

BA Book Everything plugin - the really fast and powerful Booking engine with management system for theme/site developers to create any booking or rental sites (tours, hotels, hostels, apartments, cars, events etc., or all together). **Magic one-click "Demo Setup" feature helps you get started with the plugin in minutes!**
Rates and prices system is easy to use and incredibly flexible in adjusting any price variations based on seasons (dates), week days (weekend rates, etc.), number of the guests or/and number of the days/nights.
Thanks to **integration with Elementor plugin**, you’ll be able to see BA Book Everything shortcodes and widgets (e.g. search availability form, booking form, booking item calendar, slideshow and other post elements) in the list of the content modules in the Elementor builder.
It’s highly customizable with hooks, so you need to have some programming skills to drive it on the top gear in your project. But even "out of the box" the plugin is ready for use and could be sufficient in many cases – **[Try Demo](https://ba-booking.com/ba-book-everything/search-result/?request_search_results=1)** and **[Read details](https://ba-booking.com/ba-book-everything/)**.

= Docs & Support =

You can find [Docs](https://ba-booking.com/ba-book-everything/documentation/installation/) and more detailed information about BA Book Everything on [ba-booking.com](https://ba-booking.com/ba-book-everything/). If you were unable to find the answer to your question in any of the documentation, you should check the [support forum](https://wordpress.org/support/plugin/ba-book-everything/) on WordPress.org. If you can't locate any topics that pertain to your particular issue, post a new topic for it.

= Features =
*   One-click Demo content setup
*   Booking rules for nights, days, hours or event (tour) booking
*   Custom categories (tours, hotels, apartments, cars, events etc.) with own booking rules
*   Easy to create custom taxonomies from the administrator screen to use them when editing booking objects
*   Easy to create schedule and prices
*   Flexible rate rules: minimum/maximum booking period, days of the week to which the rate applies, days of the week in which the reservation can be started
*   Variable prices based on number of the guests or/and number of the days/nights
*   Cyclic availability: schedule items availability every N days for M days. Useful for cruises and other types of rentals with cycles other than a week.
*   Exclude certain dates from availability calendar 
*   Setup prices for any age categories (customizable)
*   Search form builder: allows to use custom taxonomies as filters, price range picker, guests selection, keyword search, search tabs (different search field set per booking category)
*   Promotional: discount Coupons, discounted prices with time constraints
*   Full control on the Booking process – e-mail confirmations and notifications, customizable booking form, checkout form, option for manually availability confirmation (both email and admin dashboard)
*   Availability calendar with prices, synchronized with booking form widget
*   Widgets: booking form, search form, taxonomy terms filter, prices filter
*   Shortcode [all-items]
*   Elementor support
*   5 stars rating integrated with WP comments
*   Product schema markup
*   Service post type - sell services with booking!
*   Variable prices for services
*   FAQ post type
*   Google Maps API integrated to show address map or find nearest meeting points (for tours etc.)
*   Internationalized and Translation ready
*   Multilingual support for WPML plugin
*   Post duplication supported with Post Duplicator plugin
*   Hooks for customization (developers)
*   Integrated with WP xml export/import
*   and more...

= Available in Addons =
*   Currency switcher
*   iCal synchronization
*   PDF invoices
*   Backend bookings from dashboard: creating, editing, cancellation, manual refund (full or partial)
*   Editing/cancellation orders for both customers and administrators, extra charge, full or partial refund, custom refund rules
*   Export orders to XLSX file
*   PayPal payments
*   Stripe payment gateway for credit card payments, including 3D Secure, Apple Pay, Google Pay, Microsoft Pay and the Payment Request API
*   Authorize.net payment gateway for credit card payments
*   Razorpay payment gateway for credit card payments
*   ePayco payment gateway for credit card payments
*   Yoco payment gateway for credit card payments
*   Wompi payment gateway for credit card payments
*   Mercado Pago payment gateway for credit card payments
*   Mi Cuenta Web (IZIPAY) payment gateway
*   Kashier payment gateway for credit card payments
*   PARAM payment gateway for credit card payments (TRY)
*   IfthenPay payment gateway for Multibanco (Pag. Serviços), MB WAY, Credit card and Payshop payments
*   "Bank transfer" payment option
*   Network N-Genius payment gateway
*   Pesapal payment gateway (Kenya, Malawi, Tanzania, Rwanda, Uganda, Zambia, Zimbabwe)
*   Paymentez payment gateway
*   SimplePay payment gateway
*   Cashfree (India) payment gateway
*   Vipps payment gateway
*   OnePay payment gateway (Vietnam)
*   Transbank Webpay Plus (Chile) payment gateway
*   more on request

You can find [BA Book Everything Addons and Themes](https://ba-booking.com/shop/) and more detailed information about the plugin on [ba-booking.com](https://ba-booking.com/).

= Limitations =
The War is a fact that has already affected many businesses around the world, including ours. Until the war is over, we are not providing any products or services to the russian audience.
When using our products, russian audiences may encounter unexpected behavior.

== Installation ==

= Minimum Requirements =

* PHP 7.4 or greater
* MySQL 5.7 / MariaDB 10 or greater is recommended

= Installation =

1. Log in to your WordPress dashboard, navigate to the "Plugins" > "Add New" menu, search for "BA Book Everything" and  click "Install Now". You can also add the plugin manually as described in the [WordPress codex instructions](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation).
1. Activate the "BA Book Everything" plugin through the 'Plugins' menu in WordPress
1. Go to "BA Settings" menu to make main settings
1. Go to "BA Settings" > "Demo content" menu to setup demo content in one click
1. Go to "BA Book Everything" > "Search Form" menu to customize search form fields
1. Go to "Appearance" > "Widgets" menu to add booking form, search form and search filters to front-end pages

= Updating =

Automatic updates should work smoothly, but we still recommend you back up your site.

= Customization =
1. Start learning hooks and functions from BABE_html class.
1. [Send us a request](https://ba-booking.com/)

== Changelog ==

= 1.6.24 =
* Improved order status update during payment processing
* Added recalculate rating button to the booking object admin screen
* Added the button to calculate the remaining amount to the order admin screen

= 1.6.23 =
* Added hooks to BABE_html::booking_form_av_times method

= 1.6.22 =
* Fixed guest description of the services in orders

= 1.6.21 =
* Fixed security issues related to "My account" page functionality
* Updated Emogrifier
* Added "Pay later" text settings into the "BA Settings" > "Payments" admin menu

= 1.6.20 =
* Fixed 12-hour time format support in the booking item schedule

= 1.6.19 =
* Updated plugin URLs

= 1.6.18 =
* Set daterangepicker into autoApply mode in the search form
* Google map scripts are updated

= 1.6.17 =
* Fix language filter in booking object posts synchronization for sites with WPML plugin

= 1.6.16 =
* Fix availability calendar tooltips CSS for mobile devices

= 1.6.15 =
* Fix availability calendar cells CSS for mobile devices

= 1.6.14 =
* Fix language switching issue for sites with WPML plugin

= 1.6.13 =
* Made search form settings translatable into different languages using WPML plugin

= 1.6.12 =
* Allow to use age categories for booking items with object booking mode

= 1.6.11 =
* Fix language filter in the get_posts method for sites with WPML plugin

= 1.6.10 =
* Fix confirmation email language on sites with WPML plugin when processing IPN from a payment provider

= 1.6.9 =
* Fix issues with an insufficient output escaping

= 1.6.8 =
* Fixed request payment issue for payment_expected order status
* Fixed issue with translations not being displayed

= 1.6.7 =
* Added new options to the search form builder to set default values into "date from" and "date to" fields

= 1.6.6 =
* Fix settings for different languages when using WPML plugin
* Fix confirmation email language on sites with WPML plugin when processing IPN from a payment provider
* Added states of Portugal

= 1.6.5 =
* Fix request booking form issue for events with a single custom basic booking period (one-time events, etc.)
* Fixed vulnerability in search request params

= 1.6.4 =
* Fix confirmation email language on sites with WPML plugin when processing IPN from a payment provider
* Fix booking form javascript issue with calculation of available "date to" for booking items with "1 night" basic booking period (apartments, rooms etc.)
* [**New**] Added new option to disable guest bookings into the BA Settings > Order admin menu

= 1.6.3 =
* Added the 'Custom section' Elementor widget
* Added new hooks for customization of the DB search query, request booking form html, admin email content about custom booking request
* Fixed the search form javascript to make it possible to use the same filters in the search form and in the sidebar search filter widgets

= 1.6.2 =
* Fixed hourly booking rate calculation for multiple rates

= 1.6.1 =
* Updated the "tested up to" WP version

= 1.6.0 =
* Allow creating booking items without prices
* [**New**] Added new option to booking categories to exclude dates for all booking items associated with the booking category
* [**New**] Added request booking mode so that customer can inquire for an item price and availability via site form instead of making a reservation online.
* [**New**] Added option to the booking items to define the service selection method (choose between 'checkbox' and 'radio')

= 1.5.31 =
* Fixed merging of terms in search query
* Allow to define prices and guests without main age

= 1.5.30 =
* Replace a deprecated Elementor hook

= 1.5.29 =
* Allow coupon amount with decimal

= 1.5.28 =
* Fixed database upgrade issue

= 1.5.27 =
* Fixed an issue with filtering search results by terms

= 1.5.26 =
* Fixed an issue with ordering search results by rating

= 1.5.25 =
* Improved performance
* Added general setting "Do not include availability calendar data in search results" to improve search performance
* Fixed comment rating calculation issue
* Minor fixes

= 1.5.24 =
* Hourly booking is upgraded: added "Business time" option to allow to limit the available time in the booking form, improved rate calculation with minimum and maximum booking options, removed "date to" from the booking form

= 1.5.23 =
* Improved rates management
* [**New**] Terms of all levels of the hierarchy in the custom taxonomy can be selected in filters (check option "Make terms of all levels of the hierarchy available for selection")
* [**New**] Added "Business time" option to booking items with a booking period of "day" to allow to limit the available time in the booking form

= 1.5.22 =
* Improved rates management when calculating prices

= 1.5.21 =
* Added setting "Prefix for custom taxonomies" into "BA Settings" > "General" admin menu to allow to change the default one.

= 1.5.20 =
* Fix issue with adding conditional prices to new services
* Added setting "Disable My Account page and new user mail" into "BA Settings" > "General" admin menu

= 1.5.19 =
* Fix issue with "amount to pay" and applying coupon when using the "full" payment model

= 1.5.18 =
* Fix issue with coupon applying when not limited to categories or items

= 1.5.17 =
* Fix API endpoint issue

= 1.5.16 =
* [**New**] Discount coupons may be limited to use only with selected booking objects or booking categories
* Updated payment IPN URL

= 1.5.15 =
* Fixed deprecated method in Elementor widgets
* Added hooks for sort arguments used in search result filter and get_posts method of BABE_Post_types class

= 1.5.14 =
* Added hooks for price discount calculation methods
* Added "Minimum number of guests" option to the booking object, which is used in the guest selection dropdown on the booking form for the main age (default is 0)

= 1.5.13 =
* [**New**] Added "Included Date" options to the booking object post. If entered other dates will be excluded

= 1.5.12 =
* Conditional prices for hourly bookings are extended by the condition of the number of hours
* Added hooks for email methods
* Shortcodes can be used in email subjects

= 1.5.11 =
* Service prices with "per day" "per night" types are now calculated according to the "duration" setting in booking items with "recurrent_custom" booking period (like tours, etc.)

= 1.5.10 =
* Data sanitization improvements
* Make it possible to edit coupon field in the order

= 1.5.9 =
* Added zero quantity to service selection in the booking form
* Added a setting to define the displayed value if the service has a price of zero

= 1.5.8 =
* Fix internal caching issue, coupon issue

= 1.5.7 =
* Make it possible to use coupons multiple times or an unlimited number of times
* Allow removal of applied coupon at checkout

= 1.5.6 =
* Fix booking form javascript issue
* upgrade license to GPLv3 or later

= 1.5.5 =
* Fix Elementor widget issue "_register_controls is deprecated"

= 1.5.4 =
* Fix the maximum number of guests validation issue in the booking form

= 1.5.3 =
* Availability calendar improvements

= 1.5.2 =
* Fix available guests calculation for night booking period

= 1.5.1 =
* Fix mandatory service validation bug on the checkout

= 1.5.0 =
* [**New**] Added select quantity option for Services
* Requires at least PHP 7.4

= 1.4.28 =
* Fix booking form availability calendar issue

= 1.4.27 =
* Allow saving posts without prices
* Fix price localization in the price filter widget

= 1.4.26 =
* Made booking rules editable

= 1.4.25 =
* Extended BABE_Post_types::get_posts() arguments and all-items shortcode arguments, added 'not_scheduled' option to list items without schedule

= 1.4.24 =
* Fixed availability calendar bug with current day selection

= 1.4.23 =
* [**New**] Added conditional prices option for Services
* [**New**] Added post duplication support for Post Duplicator plugin
* [**New**] Tour booking is available in "object" booking mode, so it's possible to book the entire tour at once, regardless of the number of guests or even without specifying the number of guests

= 1.4.22 =
* [**New**] All rates and prices can be edited and cloned

= 1.4.21 =
* [**New**] Added booking object post setting "Last available booking time for the current date" to use with "1 night" booking mode and allow to book the current date
* [**New**] Added shop admin email option to the BA Settings > Emails

= 1.4.20 =
* [**New**] Added billing address fields to the checkout form
* Added 'payment_authorized' order status

= 1.4.19 =
* Some hooks added

= 1.4.18 =
* [**New**] Added hourly booking mode (1 hour basic booking period in the booking rules)
* Fix javascript translations
* Fix booking form calendar issue on daily bookings

= 1.4.17 =
* [**New**] Added filter by "date from" to the admin orders page

= 1.4.16 =
* Fix "Single radio" term selection mode render in custom taxonomies
* Updated CMB2 to latest version

= 1.4.15 =
* Fix pending coupons

= 1.4.14 =
* Remove negative amount when applying a coupon with a larger amount

= 1.4.13 =
* Removed the_content filter from email functions

= 1.4.12 =
* Updated WP export/import integration

= 1.4.11 =
* Fix availability calendar for items with 'single_custom' basic booking period

= 1.4.10 =
* Fix the checkout bug on items with 'single_custom' basic booking period

= 1.4.9 =
* Minor improvements

= 1.4.8 =
* Makes order status routing customizable

= 1.4.7 =
* Allows to get all booking items without availability check from BABE_Post_types::get_posts method

= 1.4.6 =
* Bugs fixed

= 1.4.5 =
* Bugs fixed

= 1.4.4 =
* Improved performance

= 1.4.3 =
* Minor improvements

= 1.4.2 =
* Improved cache management

= 1.4.1 =
* Fixed missed email after switching to payment_received status

= 1.4.0 =
* Refactored order status update actions and emails for easier customization

= 1.3.40 =
* Services can now be mandatory

= 1.3.39 =
* Bugs fixed

= 1.3.38 =
* Fixed a bug in calculating the total amount when applying a coupon

= 1.3.37 =
* Improved minimum/maximum number of guests management for booking rule with recurring custom booking period

= 1.3.36 =
* Fixed bug with price calculation when several rates are used
* Fixed bug with not working "Stop booking before ... hours" option
* Fixed bug with available guests calculation

= 1.3.35 =
* Bug fixed

= 1.3.34 =
* [**New**] Added support for WPML translation plugin
* Bugs fixed

= 1.3.33 =
* Updated order item styles on the checkout page
* Bumped the minimum WP required version to 5.4
* Bumped minimum supported version of PHP to 7.0

= 1.3.32 =
* Added setting to change "price from" label on the booking item page

= 1.3.31 =
* Fixed issue with calculating the minimum number of nights
* Daterangepicker texts are ready for translation

= 1.3.30 =
* Fixed issue with missing mandatory fees on checkout
* Improved search form guest field customization
* Added post_author to BABE_Post_types::get_posts method arguments

= 1.3.29 =
* Fixed issue with selecting "date to" in search form without tabs

= 1.3.28 =
* Fixed search form term selection issue, booking form available time select issue (daily bookings)
* Added new hooks

= 1.3.27 =
* Added extra guests fields to checkout form

= 1.3.26 =
* Fixed issue "CMB2_Hookup not found"

= 1.3.25 =
* Fixed XSS vulnerability in search request params
* Fixed issues based on security review

= 1.3.24 =
* Extended data set for WP export/import

= 1.3.23 =
* Fixed the issue of selecting a category when editing "booking object" post

= 1.3.22 =
* Fixed bug "incorrect datetime value" when using MySQL 8

= 1.3.21 =
* Fixed bug with "price from" calculation and sorting by price

= 1.3.20 =
* Improved performance

= 1.3.19 =
* Extended search form with keyword field, price range picker, advanced field (allows select multiple taxonomy terms via checkboxes)
* Improved performance

= 1.3.18 =
* Fixed booking form bugs: guest selection doesn't work properly with age categories, date fields aren't prefilled with dates after search request

= 1.3.17 =
* Plugin data can be exported into xml and imported to new site via standard WP export/import

= 1.3.16 =
* Added sort option "post_title" to "all-items" shortcode

= 1.3.15 =
* Updated "related items" styles and script on booking object editing page
* Removed source map links from js files

= 1.3.14 =
* Discount Coupon can be set as a percentage of the order amount
* Added category filter for selecting related items on booking object editing page

= 1.3.13 =
* Added integration with Elementor plugin

= 1.3.12 =
* Added tabindex attribute to search form fields for better keyboard navigation
* Added Product schema markup

= 1.3.11 =
* Added setting to define maximum number of months in availability calendar

= 1.3.10 =
* Fixed bug "not sending booking confirmation email"

= 1.3.9 =
* Fixed price calculation error with two or more rates applied to date range 

= 1.3.8 =
* Improved tax display in order details at checkout and in emails
* html tags are supported in email "Body message" field at "Emails" settings tab
* Added "Request Payment" button to order list on Orders admin page. It sends an email "Your order is waiting for payment" to customer
* Updated Search form styles and scripts

= 1.3.7 =
* Updated Search form styles and scripts
* Added Fontawesome class option to field settings in the Search form builder

= 1.3.6 =
* [**New**] Added Search form builder
* [**New**] Added fixed deposit amount option into the booking object post

= 1.3.5 =
* Fixed bug with "not found rates" for availability calendar when number of rates is more than 100

= 1.3.4 =
* Fixed bug "guest selection field lost in booking form"

= 1.3.3 =
* Fixed error with "Undefined offset: 0" in class-babe-html.php, class-babe-prices.php

= 1.3.2 =
* Fixed age terms sorting bug
* Fixed prices format in Demo content
* Fixed database upgrade rates issue

= 1.3.1 =
* Fixed Error: Call to a member function get_var()

= 1.3.0 =
* [**New**] Significant changes in rates and availability calendar database
* [**New**] Added to rates: minimum/maximum booking period, days of the week to which the rate applies, days of the week in which the reservation can be started
* [**New**] Added to rates: flexible prices using the rule constructor based on number of the guests or/and number of the days/nights
* [**New**] Cyclic availability: schedule items availability every N days for M days. Useful for cruises and other types of rentals with cycles other than a week.

= 1.2.9 =
* Changed default search date range to "+1 year"
* Added "date_from" and "date_to" args to [all-items] shortcode

= 1.2.8 =
* Fixed js bug with adding a schedule
* Updated styles

= 1.2.7 =
* [**New**] Added one-click Demo content setup
* Various bugs fixed

= 1.2.6 =
* [**New**] Added user registration form
* Fixed bug: the map section is displayed on the item page when it is turned off in the category
* Various bugs fixed

= 1.2.5 =
* Updated modal window styles

= 1.2.4 =
* Speed optimization
* Fixed js bug with undefined variable

= 1.2.3 =
* Fixed email styles 

= 1.2.2 =
* Added related booking objects option on booking object edit page
* Added email message settings to plugin's settings page
* Added new hooks
* Added option "Use Gutenberg for booking object posts"

= 1.2.1 =
* Fixed js bug with applying coupon on checkout

= 1.2.0 =
* [**New**] Added order statuses "canceled" and "completed"
* [**New**] Added setting to booking object to exclude certain dates from availability calendar
* [**New**] Added shortcode [all-items]
* [**New**] Ready for BABE Backoffice addon
* Added support WP local timezone for current datetime calculation
* Added some hooks
* Improved payments class with refund support
* Fixed bug with PHP 7.2 compatibility of CMB2 plugin

= 1.1.5 =
* Fixed bug with assigning taxonomies terms when updating the booking object post  

= 1.1.4 =
* [**New**] Added setting "Add services to booking form"
* [**New**] Added setting "Remove unitegallery from booking object pages"
* [**New**] Added setting "Remove google map from booking object pages"
* [**New**] Added setting "Remove availability calendar from booking object pages"
* [**New**] Added setting "Waiting before delete orders with "payment processing" status"
* Added some hooks
* Improved booking form elements
* Improved styles of checkout form elements
* Updated price calculation for 'day' basic booking period
* Fixed bug with updating the availability calendar on saving a booking object post

= 1.1.3 =
* Improved and added some hooks
* Improved booking form elements
* Added time select to booking form elements for 'day' basic booking period (useful for car booking, etc.)

= 1.1.2 =
* Ready to integrate payment gateways
* Added some hooks

= 1.1.1 =
* [**New**] Added discount Coupons.
* Fixed bug with reset password on My Account page
* Added some hooks

= 1.1.0 =
* [**New**] Added My account front end page to manage bookings for customer and manager roles.
* Meeting points addresses moved to 'place' post type
* Improved and added some filters
* Fixed bug with price calculation

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.3.0 =
1.3.0 is a major update. It is important that you make backups and ensure themes and extensions are 1.3.0 compatible before upgrading.
