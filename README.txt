=== AdScout Integration ===
Contributors: druf, adscout
Tags: adscout, adscout.io, referrals, scoutefy.com
Requires at least: 4.7
Tested up to: 6.6
Stable tag: 2.2.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The official plugin for AdScout.io integration

== Description ==

This is the official plugin for AdScout.io integration. It allows you to easily integrate AdScout.io into your WordPress/WooCommerce site to make the most of our network of trusted recommendations.

== Installation ==

1. Upload `adscout.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enter your API credentials in the AdScout settings page
1. If you have WоoCommerce enabled, generate the AdScout feed and submit it to your AdScout.io admin panel

== Frequently Asked Questions ==

= What is AdScout =

AdScout is the first social platform of its kind in Bulgaria, which builds a network of trusted recommendations, connecting satisfied users with popular brands. It is based on verified references that people are earning from. The platform enables users to share with family and friends - quickly, easily, and comfortably, any kind of products, with verified quality - while they themselves profit from it.

= What are AdScout recommendations =

A recommendation is the way in which each promoter presents his impressions of a product or service that he is satisfied with or is confident in its qualities. The testimonial can include a title, text, video, image or GIF, in depending on each person's preferences. Each promoter can create an unlimited number of testimonials using this dashboard. Once their testimonial is approved, they can share a link to the product or service from the brand's website.

= How can AdScout help me win new customers with confidence? =

AdScout is a marketing platform that relies on trusted recommendations from satisfied users. It enables your customers to share your products with their loved ones and earn from it, ultimately helping you increase your sales.

= What steps do I need to follow to get started on AdScout for business? =

Creating a FREE account on AdScout for business is easy and quick. Just follow the indicated steps, which will only take a few minutes. After that, we'll take care of the rest!

== External services ==
 Our plugin uses two different domains for our website: adscout.io and scoutefy.com. While the content on both domains is the same, we utilize the domain scoutefy.com to minimize the chances of AdBlockers blocking our scripts, as it doesn't contain the word "ad." This is why we are using scoutefy.com for all integration purposes. This is our own internal service withe seperate domain. The same [terms and conditions](https://scoutefy.com/terms-and-conditions) and [privacy policy](https://scoutefy.com/privacy-policy) apply.

== Changelog ==

= 2.2.6 =
* Modified category separator in product feed generator

= 2.2.5 =
* Hotfix: fixed delete action

= 2.2.4 =
* Hotfix: modified data encryption algorithm to fix a loophole in storage

= 2.2.3 =
* Updated documentation to reflect usage of scoutefy.com in the module

= 2.2.2 =
* Small code modifications based on initial plugin upload feedback

= 2.2.1 =
* Added detailed error and warning feedbacks

= 2.2.0 =
* Added sync for avaliable order statuses in store
* Performance optimizations

= 2.1.0 =
* Changed options encryption method to eliminate potential errors
* Added options to select which order statuses are to be synced with AdScout
* Added storage for AdScout cookie in order meta to remove faulty syncs with AdScout

= 2.0.4 =
* Updated order processing to almost finalise order logic

= 2.0.3 =
* Minor bugfixes

= 2.0.2 =
* Updated order processing to remove faulty logic

= 2.0.1 =
* Updated options storage for additional information

= 2.0.0 =
* Plugin refactoring
* All settings are stored encrypted for better security and privacy
* Added improved support for WooCommerce for better tracking
* Improved plugin performance and speed
* Added custom logger for better and more secure logging of events for seamless support

== Upgrade Notice ==

= 2.0.0 =
Please, back up your API Token and API Code before upgrading.
