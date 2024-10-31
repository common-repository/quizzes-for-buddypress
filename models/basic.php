<?php
if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// main model containing general config and UI functions
class QBuddy {
   static function install($update = false) {
   	global $wpdb;	
   	$wpdb -> show_errors();
   	if(!$update) self::init();
   	
	 // relations bewteen completed exams and groups   
    if($wpdb->get_var("SHOW TABLES LIKE '".QBUDDY_RULES."'") != QBUDDY_RULES) {  
        $sql = "CREATE TABLE `".QBUDDY_RULES."` (
				id int(11) unsigned NOT NULL auto_increment PRIMARY KEY,
				test_id int(11) unsigned NOT NULL default '0',
				group_id int(11) unsigned NOT NULL default '0',
				grade_ids VARCHAR(100) NOT NULL DEFAULT '',
				percent_correct INT UNSIGNED NOT NULL DEFAULT '0',
				require_to_join TINYINT UNSIGNED NOT NULL DEFAULT 0,
				require_to_view TINYINT UNSIGNED NOT NULL DEFAULT 0
			) CHARACTER SET utf8;";
        $wpdb->query($sql);         
    	}
    	
    	self :: add_db_fields(array(
    			array('name' => 'add_to_group', 'type'=>'TINYINT UNSIGNED NOT NULL DEFAULT 0'),
    		),
    		QBUDDY_RULES);
   	
   	update_option('qbuddy_version', '0.03');   	
	
	} // end install	   
	
	// initialization
	static function init() {
		global $wpdb;
		load_plugin_textdomain( 'qbuddy' );
		define('QBUDDY_RULES', $wpdb->prefix . 'qbuddy_rules');
			
		add_action('wp_enqueue_scripts', array(__CLASS__, 'scripts'));
		add_action( 'admin_menu', array(__CLASS__, 'admin_menu') );
		
		// ajax
		add_action('wp_ajax_qbuddy_ajax', array('QBuddyAjax', 'dispatch'));
		add_action('wp_ajax_nopriv_qbuddy_ajax', array('QBuddyAjax', 'dispatch'));

		// completed exam		
		$integration_mode = get_option('qbuddy_integration_mode');
		switch($integration_mode) {
			case 'watupro': $action_hook = 'watupro_completed_exam'; break;
			case 'watu': $action_hook = 'watu_exam_submitted'; break;
			case 'chained_quiz': $action_hook = 'chained_quiz_completed'; break;
		}
		
		if(!empty($action_hook)) add_action($action_hook, array('QBuddyActions', 'completed_exam'));
		
		add_action('groups_member_before_save', array('QBuddyActions', 'before_join_group'));
	
		$version = get_option('qbuddy_version');
		if($version < 0.03) self ::install(true);
	}	
	
	// CSS and JS
	static function scripts() {   
   	wp_enqueue_script('jquery');
   
	}
	
	static function admin_menu() {
		add_menu_page(__('Quizzes for BuddyPress', 'qbuddy'), __('Quizzes for BuddyPress', 'qbuddy'), 'manage_options', 'qbuddy', array(__CLASS__, 'options'));
		add_submenu_page('qbuddy', __('Settings', 'qbuddy'), __('Settings', 'qbuddy'), 'manage_options', 'qbuddy', array(__CLASS__, 'options'));
		add_submenu_page('qbuddy', __('Rules', 'qbuddy'), __('Rules', 'qbuddy'), 'manage_options', 'qbuddy_rules', array('QBuddyRules', 'manage'));
	}
	
	
	static function options() {
		global $wpdb;
		
		if(!empty($_POST['save_options']) and check_admin_referer('qbuddy_options')) {
			update_option('qbuddy_integration_mode', sanitize_text_field($_POST['integration_mode']));
		}
		
		$integration_mode = get_option('qbuddy_integration_mode');
		include(QBUDDY_PATH . '/views/main.html.php');
	} // end options()
	
	// function to conditionally add DB fields
	static function add_db_fields($fields, $table) {
		global $wpdb;
		
		// check fields
		$table_fields = $wpdb->get_results("SHOW COLUMNS FROM `$table`");
		$table_field_names = array();
		foreach($table_fields as $f) $table_field_names[] = $f->Field;		
		$fields_to_add=array();
		
		foreach($fields as $field) {
			 if(!in_array($field['name'], $table_field_names)) {
			 	  $fields_to_add[] = $field;
			 } 
		}
		
		// now if there are fields to add, run the query
		if(!empty($fields_to_add)) {
			 $sql = "ALTER TABLE `$table` ";
			 
			 foreach($fields_to_add as $cnt => $field) {
			 	 if($cnt > 0) $sql .= ", ";
			 	 $sql .= "ADD $field[name] $field[type]";
			 } 
			 
			 $wpdb->query($sql);
		}
}
}