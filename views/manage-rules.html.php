<div class="wrap">
	<h1><?php _e('Manage Rules', 'qbuddy');?></h1>
	
	<p><?php _e('Here you can define your BuddyPress group rules in relation to quizzes. You can add multiple rules for each group.', 'qbuddy');?></p>
	
	<form method="post" onsubmit="return validateQRule(this);">
		<p><?php _e('Group:', 'qbuddy');?> <select name="group_id">
			<?php foreach($groups['groups'] as $group):?>
				<option value="<?php echo $group->id?>"><?php echo stripslashes($group->name);?></option>
			<?php endforeach;?>	
			</select>
			<?php _e('Quiz:', 'qbuddy');?> <select name="test_id" onchange="qbuddySelectQuiz(this);">
				<option value=""><?php _e('- select quiz -', 'qbuddy');?></option>
				<?php foreach($tests as $test):?>
					<option value="<?php echo $test->{$id_field}?>"><?php echo stripslashes($test->{$name_field});?></option>
				<?php endforeach;?>
			</select>
			<?php _e('Grades/Results:', 'qbuddy');?> <select name="grade_ids[]" multiple="true" size="4">
				<option value=""><?php _e('- select quiz -', 'qbuddy');?></option>
			</select>
			<?php _e('Min. percent correct answer:', 'qbuddy');?>
			<input type="text" name="percent_correct" size="4">
			<input type="submit" value="<?php _e('Add rule', 'qbuddy');?>">
			<br>
			<?php _e('Actions & conditions:', 'qbuddy');?>
				<input type="checkbox" name="add_to_group" value="1"> <?php _e('Automatically add user to the group.', 'qbuddy');?>
				<input type="checkbox" name="require_to_join" value="1"> <?php _e('The rule is required to join the group.', 'qbuddy');?>				
			</p>
			<input type="hidden" name="add" value="1">
			<?php wp_nonce_field('qbuddy_rules');?>
	</form>
	
	<?php if(count($rules)):?>
		<h2><?php _e('Manage Existing Rules', 'qbuddy');?></h2>
		
		<?php foreach($rules as $rule):?>
		<form method="post" onsubmit="return validateQRule(this);">
			<p><?php _e('Group:', 'qbuddy');?> <select name="group_id">
				<?php foreach($groups['groups'] as $group):
					$selected = ($rule->group_id == $group->id) ? ' selected' : '';?>
					<option value="<?php echo $group->id?>" <?php echo $selected?>><?php echo stripslashes($group->name);?></option>
				<?php endforeach;?>	
				</select>
				<?php _('Quiz:', 'qbuddy');?> <select name="test_id" onchange="qbuddySelectQuiz(this);">					
					<?php foreach($tests as $test):
						$selected = ($rule->test_id == $test->{$id_field}) ? ' selected' : '';?>
						<option value="<?php echo $test->{$id_field}?>" <?php echo $selected?>><?php echo stripslashes($test->{$name_field});?></option>
					<?php endforeach;?>
				</select>
				<?php _e('Grades/Results:', 'qbuddy');?> <select name="grade_ids[]" multiple="true" size="4">
					<option value=""><?php _e('- Any grade -', 'qbuddy');?></option>
					<?php foreach($rule->grades as $grade):
					$rule_grade_ids = explode(',', $rule->grade_ids);
					if(in_array($grade->{$id_field}, $rule_grade_ids)) $selected = ' selected';
					else $selected = '';?>
					<option value="<?php echo $grade->{$id_field}?>" <?php echo $selected?>><?php echo stripslashes($grade->{$grade_title_field});?></option>
					<?php endforeach;?>
				</select>
				<?php _e('Min. percent correct answer:', 'qbuddy');?>
				<input type="text" name="percent_correct" size="4" value="<?php echo $rule->percent_correct?>">
				<input type="submit" value="<?php _e('Save rule', 'qbuddy');?>">
				<input type="button" onclick="if(confirm('<?php _e('Are you sure?', 'qbuddy');?>')) {this.form.del.value=1;this.form.submit();}" value="<?php _e('Delete rule', 'qbuddy');?>">
				<br>
				<?php _e('Actions & conditions:', 'qbuddy');?>
					<input type="checkbox" name="add_to_group" value="1" <?php if(!empty($rule->add_to_group)) echo 'checked'?> > <?php _e('Automatically add user to the group.', 'qbuddy');?>
					<input type="checkbox" name="require_to_join" value="1" <?php if(!empty($rule->require_to_join)) echo 'checked'?>> <?php _e('The rule is required to join the group.', 'qbuddy');?>
				</p>
				<input type="hidden" name="save" value="1">
				<input type="hidden" name="del" value="0">
				<input type="hidden" name="id" value="<?php echo $rule->id?>">
				<?php wp_nonce_field('qbuddy_rules');?>
		</form>
		<?php endforeach;?>
	<?php endif;?>
</div>

<script type="text/javascript">
function qbuddySelectQuiz(fld) {	
	var val = fld.value;
	var frm = fld.form;	
	var url = ajaxurl;
	data = {'action' : 'qbuddy_ajax', 'do' : 'select_quiz', 'test_id' : val};
	
	jQuery.post(url, data, function(msg) {		
		jQuery(frm.elements['grade_ids[]']).html(msg);
	});	
}

function validateQRule(frm) {
	if(frm.test_id.value == '') {
		alert("<?php _e('You must select a quiz.','qbuddy');?>");
		frm.test_id.focus();
		return false;
	}
	return true;
}
</script>