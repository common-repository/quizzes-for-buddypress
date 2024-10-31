<?php
// manage rules
class QBuddyRules {
	static function manage() {
		global $wpdb;
		$integration_mode = get_option('qbuddy_integration_mode');
		
		if($integration_mode == 'watu') {
			$table = WATU_EXAMS;
			$grades_table = WATU_GRADES;
			$name_field = 'name';
			$id_field = 'ID';
			$grade_test_field = 'exam_id';
			$grade_title_field = 'gtitle';
		}
		
		if($integration_mode == 'watupro') {
			$table = WATUPRO_EXAMS;
			$grades_table = WATUPRO_GRADES;
			$name_field = 'name';
			$id_field = 'ID';
			$grade_test_field = 'exam_id';
			$grade_title_field = 'gtitle';
		}
		
		if($integration_mode == 'chained_quiz') {
			$table = CHAINED_QUIZZES;
			$grades_table = WATUPRO_RESULTS;
			$name_field = 'title';
			$id_field = 'ID';
			$grade_test_field = 'result_id';
			$grade_title_field = 'title';
		}
		
		if(!empty($_POST['add']) or !empty($_POST['save'])) {
			// prepare vars 
			$test_id = intval($_POST['test_id']);
			$group_id = intval($_POST['group_id']);
			$grade_ids = implode(',', qbuddy_int_array($_POST['grade_ids']));
			$percent_correct = intval($_POST['percent_correct']);
			$require_to_join = empty($_POST['require_to_join']) ? 0 : 1;
			$require_to_view = empty($_POST['require_to_view']) ? 0 : 1;
			$add_to_group = empty($_POST['add_to_group']) ? 0 : 1;
		}
		
		if(!empty($_POST['add']) and check_admin_referer('qbuddy_rules')) {
			$wpdb->query($wpdb->prepare("INSERT INTO ".QBUDDY_RULES." SET
					test_id=%d, group_id=%d, grade_ids=%s, percent_correct=%d,
					require_to_join=%d, require_to_view=%d, add_to_group=%d",
					$test_id, $group_id, $grade_ids, $percent_correct,
					$require_to_join, $require_to_view, $add_to_group));
		}
		
		if(!empty($_POST['del']) and check_admin_referer('qbuddy_rules')) {
			$wpdb->query($wpdb->prepare("DELETE FROM ".QBUDDY_RULES." WHERE id=%d", intval($_POST['id'])));
			qbuddy_redirect('admin.php?page=qbuddy_rules');
		}
		
		if(!empty($_POST['save']) and check_admin_referer('qbuddy_rules')) {
			$wpdb->query($wpdb->prepare("UPDATE ".QBUDDY_RULES." SET
					test_id=%d, group_id=%d, grade_ids=%s, percent_correct=%d,
					require_to_join=%d, require_to_view=%d, add_to_group=%d WHERE id=%d",
					$test_id, $group_id, $grade_ids, $percent_correct,
					$require_to_join, $require_to_view, $add_to_group, intval($_POST['id'])));
		}
		
		// select tests
		$tests = $wpdb->get_results("SELECT $name_field, $id_field FROM $table ORDER BY $name_field");
				
		// select groups
		if(bp_is_active( 'groups' )) {
			$groups = BP_Groups_Group::get(array(
							'type'=>'alphabetical',
							'per_page'=>999
							));
		}
		else $groups = null;		
							
		// select existing rules 
		$rules = $wpdb->get_results("SELECT tR.*, tT.$name_field as test_name
			FROM ".QBUDDY_RULES." tR JOIN $table tT ON tT.$id_field=tR.test_id");
			
		// fill grades for each rule
		foreach($rules as $cnt => $rule) {
			$grades = $wpdb->get_results($wpdb->prepare("SELECT $id_field, $grade_title_field 
				FROM $grades_table WHERE $grade_test_field=%d ORDER BY $grade_title_field", $rule->test_id));
				
			$rules[$cnt]->grades = $grades; 	
		}					
							
		include(QBUDDY_PATH . '/views/manage-rules.html.php');					
	} // end manage()
}