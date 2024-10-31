<?php
/*
Plugin Name: Quizzes for BuddyPress
Plugin URI: http://blog.calendarscripts.info/quizzes-for-buddypress/ 
Description: Use the quizzes from powerful third party quiz plugins in BuddyPress. Currently supports <a href="https://wordpress.org/plugins/watu/">Watu</a>, <a href="http://calendarscripts.info/watupro/">WatuPRO</a>, and <a href="https://wordpress.org/plugins/chained-quiz/">Chained Quiz</a>
Author: Kiboko Labs
Version: 0.7.0.3
Author URI: http://kibokolabs.com
License: GPLv2 or later
Text-domain: watulp
*/

define( 'QBUDDY_PATH', dirname( __FILE__ ) );
define( 'QBUDDY_RELATIVE_PATH', dirname( plugin_basename( __FILE__ )));
define( 'QBUDDY_URL', plugin_dir_url( __FILE__ ));

// require controllers and models
require_once(QBUDDY_PATH.'/models/basic.php');
//require_once(QBUDDY_PATH.'/controllers/bridge.php');
require_once(QBUDDY_PATH.'/controllers/rules.php');
require_once(QBUDDY_PATH.'/controllers/actions.php');
require_once(QBUDDY_PATH.'/controllers/ajax.php');
require_once(QBUDDY_PATH.'/helpers/common.php');

add_action('init', array("QBuddy", "init"));

register_activation_hook(__FILE__, array("QBuddy", "install"));

// add user to group with function groups_join_group( $group_id, $user_id = 0 )