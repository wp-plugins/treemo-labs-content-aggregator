<?php
/*
Plugin Name: Treemo Labs Content Aggregator
Plugin URI: http://wordpress.org/extend/plugins/treemo-labs-content-aggregator/
Description: Required plugin to participate in the Treemo Labs Content Aggregation platform.
Version: 0.7.7.5
Modifications By: Josh Schumacher
Original Author: Dan Phiffer
*/

// @TODO Define default
define(DEFAULT_AGGREGATOR_NOTIFICATION_API, '');

$dir = treemo_json_api_dir();
@include_once "$dir/singletons/api.php";
@include_once "$dir/singletons/query.php";
@include_once "$dir/singletons/introspector.php";
@include_once "$dir/singletons/response.php";
@include_once "$dir/models/post.php";
@include_once "$dir/models/comment.php";
@include_once "$dir/models/category.php";
@include_once "$dir/models/tag.php";
@include_once "$dir/models/author.php";
@include_once "$dir/models/attachment.php";

function treemo_json_api_init() {
  global $treemo_json_api;
  if (phpversion() < 5) {
    add_action('admin_notices', 'treemo_json_api_php_version_warning');
    return;
  }
  if (!class_exists('TREEMO_JSON_API')) {
    add_action('admin_notices', 'treemo_json_api_class_warning');
    return;
  }
  add_filter('rewrite_rules_array', 'treemo_json_api_rewrites');
  $treemo_json_api = new TREEMO_JSON_API();
}

function treemo_json_api_php_version_warning() {
  echo "<div id=\"json-api-warning\" class=\"updated fade\"><p>Sorry, JSON API requires PHP version 5.0 or greater.</p></div>";
}

function treemo_json_api_class_warning() {
  echo "<div id=\"json-api-warning\" class=\"updated fade\"><p>Oops, TREEMO_JSON_API class not found. If you've defined a TREEMO_JSON_API_DIR constant, double check that the path is correct.</p></div>";
}

function treemo_api_notify_mothership($action, $params = array()) {
	$mothership = get_option('treemo_json_api_notification_api' DEFAULT_AGGREGATOR_NOTIFICATION_API);
	if (empty($mothership))
		return false;
	
	$core = new TREEMO_JSON_API_Core_Controller();
	$info = $core->info();
	$params['secret'] = get_option('treemo_json_api_secret');
	$params['version'] = $info['treemo_json_api_version'];
	$params['api_url'] = bloginfo('url').'/'.get_option('treemo_json_api_base', 'api');
	
	$ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_exec($ch);
    curl_close($ch);
}

function treemo_json_api_activation() {
  if (class_exists('JSON_API')) {
    wp_die("<h1>This plugin is not compatible with the JSON API plugin</h1><p> Please first deactivate the JSON API before activating this plugin.</p>", "Error activating plugin", array('back_link'=>true));
  }
  
  // Create a secret for safe communiction between Aggregator and WP Instance
  $secret = md5(microtime(true)+rand());
  add_option('treemo_json_api_secret', $secret);
  
  // Notify Aggregator that this node is now online and you should start syncing
  $params = array(
  	'public_url' => bloginfo('url'),
  	'description' => bloginfo('description'),
  	'name' => bloginfo('name')
  );
  treemo_api_notify_mothership('register', $params);

  // Add the rewrite rule on activation
  global $wp_rewrite;
  add_filter('rewrite_rules_array', 'treemo_json_api_rewrites');
  $wp_rewrite->flush_rules();
}

function treemo_json_api_deactivation() {
  // Remove the rewrite rule on deactivation
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}

function treemo_json_api_rewrites($wp_rules) {
  $base = get_option('treemo_json_api_base', 'api');
  if (empty($base)) {
    return $wp_rules;
  }
  $treemo_json_api_rules = array(
    "$base\$" => 'index.php?json=info',
    "$base/(.+)\$" => 'index.php?json=$matches[1]'
  );
  return array_merge($treemo_json_api_rules, $wp_rules);
}

function treemo_json_api_dir() {
  if (defined('TREEMO_JSON_API_DIR') && file_exists(TREEMO_JSON_API_DIR)) {
    return TREEMO_JSON_API_DIR;
  } else {
    return dirname(__FILE__);
  }
}

function treemo_json_api_action_save_post(post_id) {
  $post = get_post($post_id);
  $params = array(
	'slug' => $post->post_name
  );
  treemo_api_notify_mothership('save_post', $params);
}

function treemo_json_api_action_deleted_post(post_id) {
  $post = get_post($post_id);
  $params = array(
  	'slug' => $post->post_name
  );
  treemo_api_notify_mothership('deleted_post', $params);
}

function treemo_json_api_action_trashed_post(post_id) {
  $post = get_post($post_id);
  $params = array(
  	'slug' => $post->post_name
  );
  treemo_api_notify_mothership('trashed_post', $params);
}

function treemo_json_api_action_untrashed_post($post_id) {
  $post = get_post($post_id);
  $params = array(
  	'slug' => $post->post_name
  );
  treemo_api_notify_mothership('untrashed_post', $params);
}

// Add initialization and activation hooks
add_action('init', 'treemo_json_api_init');
register_activation_hook("$dir/treemo-labs-content-aggregator.php", 'treemo_json_api_activation');
register_deactivation_hook("$dir/treemo-labs-content-aggregator.php", 'treemo_json_api_deactivation');

// Add hooks for when a post is published, modified or moved to the trash to notify the aggregator
add_action('save_post', 'treemo_json_api_action_save_post');
add_action('deleted_post', 'treemo_json_api_action_deleted_post');
add_action('trashed_post', 'treemo_json_api_action_trashed_post');
add_action('untrashed_post', 'treemo_json_api_action_untrashed_post');

?>