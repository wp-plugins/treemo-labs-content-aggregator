=== Treemo Labs Content Aggregator ===
Contributors: joshs633
Original Author: dphiffer
Tags: treemo labs, api, aggregation
Requires at least: 2.9
Tested up to: 3.2.1
Stable tag: 0.9.1


== Original Documentation ==
http://wordpress.org/extend/plugins/json-api/other_notes/

== Features ==
* A `secret` is required for all communication
* Registers with a central notification api on activation. You can modify or remove the notification api in the plugin settings. When you modify the api url in the plugin settings, the plugin automatically registers with the new server.
* Request is sent to the notification api when a post is published, published post is modified or a post is unpublished
* Bulk requests to fetch posts between two dates
* Bulk requests to fetch posts modified between two dates
* Bulk requests to fetch posts deleted between two dates

== Issues ==
* Production Notification API Server is not live, currently pointed at a developement version
* Does not correctly notify notification api after version upgrades
* Documentation needed for aggregator protocol
