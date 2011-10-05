<?php
/*
Plugin Name: Treemo Labs Content Aggregator
Plugin URI: http://wordpress.org/extend/plugins/treemo-labs-content-aggregator/
Description: Required plugin to participate in the Treemo Labs Content Aggregation platform.
Version: 0.7.5
Modifications By: Josh Schumacher
Original Author: Dan Phiffer
*/

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
  if (!class_exists('JSON_API')) {
    add_action('admin_notices', 'treemo_json_api_class_warning');
    return;
  }
  add_filter('rewrite_rules_array', 'treemo_json_api_rewrites');
  $treemo_json_api = new JSON_API();
}

function treemo_json_api_php_version_warning() {
  echo "<div id=\"json-api-warning\" class=\"updated fade\"><p>Sorry, JSON API requires PHP version 5.0 or greater.</p></div>";
}

function treemo_json_api_class_warning() {
  echo "<div id=\"json-api-warning\" class=\"updated fade\"><p>Oops, JSON_API class not found. If you've defined a JSON_API_DIR constant, double check that the path is correct.</p></div>";
}

function treemo_json_api_activation() {
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
  $base = get_option('treemo_json_api_base', 'treemo_api');
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
  if (defined('JSON_API_DIR') && file_exists(JSON_API_DIR)) {
    return JSON_API_DIR;
  } else {
    return dirname(__FILE__);
  }
}

// Add initialization and activation hooks
add_action('init', 'treemo_json_api_init');
register_activation_hook("$dir/treemo-labs-content-aggregator.php", 'treemo_json_api_activation');
register_deactivation_hook("$dir/treemo-labs-content-aggregator.php", 'treemo_json_api_deactivation');

?>
