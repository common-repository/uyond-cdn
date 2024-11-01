=== Uyond CDN ===
Contributors: uyond
Tags: cdn, optimize, minify, performance, pagespeed, images, lazy-load
Requires at least: 4.4
Tested up to: 5.8
Requires PHP: 5.3
License: GNU
Stable tag: trunk

Uyond CDN speeds up your site by changing your static assets to be served from the global Uyond Content Delivery Network (CDN).

== Description ==
Uyond CDN is a high performance CDN (Content Delivery Network) service that built for WordPress.  We have nodes across the globe.  This service is free with unlimited access and unlimited traffic meters.

This plugin connects you to the 3rd party Uyond CDN service.  Your assets files (js, css, and images) will be redirected to the CDN automatically.

Uyond CDN is a free CDN service.  Visit [here](https://www.uyond.com/cdn) for more details.

== Installation ==
You are recommended to install this from the \"Plugins > Add New\" page in your Wordpress admin panel.  Search for \'Uyond CDN\".

Manual installation is also available.
1. Upload the zip file and unzip it in the `/wp-content/plugins/` directory
2. Activate the plugin through the \'Plugins\' menu in WordPress
3. Go to `Settings > Uyond CDN` to register your domain.  You are all set

== Frequently Asked Questions ==
= How much does this cost? =

It's free.  Uyond provides other paid services including Wordpress hosting and Wordpress management.  That\'s how the company make money.  We have no intention to monetize this CDN service.  We want to give something back to the Wordpress community by offering this free service.

= Why do I need CDN? =

You can serve your static content faster.  Uyond CDN has nodes everywhere in the globe.  Contents will be sent to visitors from the closest nodes to the visitors.  The shorter the distance, the faster the content is being sent.  Also, your server will have less pressure by off loading all these to a third party servers.  Getting less GET request to your server means you can handle more visitors with the same hardware.
We are not aim to make any money by offering this CDN.

== About Uyond CDN ==
* [Uyond CDN](https://www.uyond.com)
* [Terms of Service](https://www.uyond.com/tos)

== Changelog ==

= Version 1.0.9 =
* Fix setup wizard routing Bug
 
= Version 1.0.8 =
* Purge with secret key protection

= Verison 1.0.7 =
* Secret key protection

= Version 1.0.5 =
* Support subfolder site url

= Verison 1.0.4 =
* Exclude Video File

= Version 1.0.3 =
* Bug Fix: Don't rewrite url when domain is not registered

= Version 1.0.0 =
* Launch