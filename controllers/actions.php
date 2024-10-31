<?php
// catches the actions from the quiz plugins
class QBuddyActions {
	static function completed_exam($taking_id) {		
		global $wpdb, $user_ID;
		$integration_mode = get_option('qbuddy_integration_mode');
		if(empty($user_ID)) return false;
		
		if(!in_array($integration_mode, array('watu', 'watupro', 'chained_quiz'))) return false;
		
		if($integration_mode == 'watu') {
			$table = WATU_TAKINGS;
			$field = 'ID';
			$exam_field = 'exam_id';
			$grade_field = 'grade_id';
		}
		
		if($integration_mode == 'watupro') {
			$table = WATUPRO_TAKEN_EXAMS;
			$field = 'ID';
			$exam_field = 'exam_id';
			$grade_field = 'grade_id';
		}
		
		if($integration_mode == 'chained_quiz') {
			$table = CHAINED_COMPLETED;
			$field = 'id';
			$exam_field = 'quiz_id';
			$grade_field = 'result_id';
		}
		
		// get taking
		$taking = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE $field=%d", $taking_id));
		
		// no taking for some reason? For example this won't work if the quiz is selected to not store results.
		// it's good to wipe out these quizzes yet on the selection page
		if(empty($taking->{$field})) return false;
		
		// are there rules to join group based on this taken result?
		$rules = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".QBUDDY_RULES."
			WHERE test_id = %d ORDER BY id", $taking->{$exam_field}));

		// now for each rule check if extra conditions are satisfied. If yes, join group
		foreach($rules as $rule) {
			if(!empty($rule->grade_ids)) {
				$grade_ids = explode(',', $rule->grade_ids);
				if(!in_array($taking->grade_id, $grade_ids)) continue; // rule not satisfied
			}
			
			if(!empty($rule->percent_correct) and $taking->percent_correct < $rule_percent_correct) continue; // rule not satisfied
			
			// if we reached this point the rule is satisfied, join the group
			self :: join_group($rule->group_id, $user_ID);
		}			
	} // end completed_exam
	
	// let's make a wrapper so we can log these actions in future versions
	static function join_group($group_id, $user_id) {
		if(function_exists('groups_join_group')) groups_join_group( $group_id, $user_id);
	}
	
	// check for unsatisfied quiz requirements before joining a group
	// @param $join is the user to group relation as it comes from BP_Groups_Member -> save()
	static function before_join_group($join) {
		global $wpdb;
		$integration_mode = get_option('qbuddy_integration_mode');
		
		if(!in_array($integration_mode, array('watu', 'watupro', 'chained_quiz'))) return false;
		
		$in_progress_sql = ""; // for WatuPRO
		
		if($integration_mode == 'watu') {
			$table = WATU_TAKINGS;
			$grades_table = WATU_GRADES;
			$exams_table = WATU_EXAMS;
			$field = 'ID';
			$exam_field = 'exam_id';
			$grade_field = 'grade_id';
			$exam_name_field = 'name';
			$grade_name_field = 'gtitle';
		}
		
		if($integration_mode == 'watupro') {
			$table = WATUPRO_TAKEN_EXAMS;
			$grades_table = WATUPRO_GRADES;
			$exams_table = WATUPRO_EXAMS;
			$field = 'ID';
			$exam_field = 'exam_id';
			$grade_field = 'grade_id';
			$in_progress_sql = " AND in_progress=0 ";
			$exam_name_field = 'name';
			$grade_name_field = 'gtitle';
		}
		
		if($integration_mode == 'chained_quiz') {
			$table = CHAINED_COMPLETED;
			$grades_table = CHAINED_RESULTS;
			$exams_table = CHAINED_QUIZZES;
			$field = 'id';
			$exam_field = 'quiz_id';
			$grade_field = 'result_id';
			$exam_name_field = 'title';
			$grade_name_field = 'title';
		}
		
		// check if there are rules for this group
		$rules = $wpdb->get_results($wpdb->prepare("SELECT tR.*, tQ.$exam_name_field as test_name
			FROM ".QBUDDY_RULES." tR JOIN $exams_table tQ ON tQ.$field=tR.test_id
			WHERE tR.group_id = %d AND tR.require_to_join=1 ORDER BY tR.id", $join->group_id));
		
		$unsatisfied_rules = array();
		
		foreach($rules as $rule) {			
			$grade_sql = "";
			if(!empty($rule->grade_ids)) {				
				$grade_sql = " AND $grade_field IN (".$rule->grade_ids.") ";
			}
			
			// is this rule satisfied?
			$is_ok = $wpdb->get_var($wpdb->prepare("SELECT $field FROM $table 
				WHERE $exam_field=%d $grade_sql AND user_id=%d $in_progress_sql", $rule->test_id, $join->user_id));
				
			if(!$is_ok)	{
				// select grades
				if(!empty($rule->grade_ids)) {
					$grades = $wpdb->get_results("SELECT * FROM $grades_table WHERE $field IN (".$rule->grade_ids.") ORDER BY $grade_name_field");
					$rule->grades = $grades;
				}				
				
				$unsatisfied_rules[] = $rule;
			}
		}	

		// in case there are unsatisfied rules, construct a string and output the data
		if(empty($unsatisfied_rules)) return true;
		
		$warning = __('You need to complete the following tests successfully before you can join this group:', 'qbuddy') . ' <ul>';
		
		foreach($unsatisfied_rules as $rule) {
			$warning .= '<li>'.stripslashes($rule->test_name);

			if(!empty($rule->grades)) {
				$grades_var = '';
				foreach($rule->grades as $cnt=>$grade) {
					if($cnt) $grades_var .= ', ';
					$grades_var .= stripslashes($grade->{$grade_name_field});	
				}				
				$warning .= ' '.sprintf(__('with any of the following results: %s', 'qbuddy'), $grades_var);
			}			
			
			$warning .= "</li>\n";
		}
		
		$warning .= '</ul><p align="center"><a href="#" onclick="history.back();">'.__('Go Back', 'qbuddy').'</a></p>';
		
		wp_die($warning);		
	} // end before_join_group
}