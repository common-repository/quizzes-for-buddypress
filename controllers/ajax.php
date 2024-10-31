<?php
// handle Ajax calls
class QBuddyAjax {
	static function dispatch() {		
		switch(@$_POST['do']) {
			case 'select_quiz': self :: select_quiz(); break;
		}
		
		exit;
	} // end dispatch
	
	// select quiz in the drop-down on the Manage rules page
	static function select_quiz() {
		global $wpdb;
		
		$integration_mode = get_option('qbuddy_integration_mode');
		
		$quiz_id = intval(@$_POST['test_id']);
		if(empty($quiz_id)) return '';
		
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
		
		if($integration_mode == 'watupro') {
			if(!class_exists('WTPGrade')) return '';
			
			$quiz = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE $id_field=%d", $quiz_id));
			$grades = WTPGrade :: get_grades($quiz);
		}		
		else {
			// directly select from DB
			$grades = $wpdb->get_results($wpdb->prepare("SELECT $id_field, $grade_title_field FROM $table 
				WHERE $grade_test_field=%d ORDER BY $grade_title_field", $quiz_id));
		}
		
		// construct options HTML
		$html = '<option value="">'.__('- Any grade/result -', 'qbuddy').'</option>'."\n";
		foreach($grades as $grade) {
			$html .= "<option value='".$grade->{$id_field}."'>".stripslashes($grade->{$grade_title_field})."</option>\n";
		}
		
		echo $html;		
	} // end select_quiz
}