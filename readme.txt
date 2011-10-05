=== Treemo Labs Content Aggregator ===
Contributors: joshs633
Original Author: dphiffer
Tags: treemo labs
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: 0.7.6.1

=== Modifications From Original Plugin ===
* singletons/query.php   
	
=== Original Documentation ===
http://wordpress.org/extend/plugins/json-api/other_notes/


=== Notes For Syncing Articles ===
__Sample Query__
 - Get new posts between timeframe
    http://blog.joshschumacher.com/api/get_recent_posts/?exclude=comments&post_type=post&author_meta=email&before_date=1256335180&after_date=1240561966&dev=1
    
__Sync Process__
* Updates
 - Get content updated since the last sync time but posted before the last sync time and updated before start of this sync time
 - Get content modified since X and the status has changed from published to remove deleted content
 - Example: Get posts modified between now and yesterday but originally posted before yesterday
       http://blog.joshschumacher.com/api/get_recent_posts/?dev=1&exclude=comments&post_type=post&author_meta=email&before_date=now&after_date=yesterday&search_type=modified&posted_before=yesterday
