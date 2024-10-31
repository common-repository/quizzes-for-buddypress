<div class="wrap">
	<h1><?php _e('Quiz Connector for BuddyPress', 'qbuddy')?></h1>
	
	<p><?php _e('This connector will allow you to specify various rules regarding quizzes and BuddyPress groups:', 'qbuddy');?></p>
	
	<ul>
		<li><?php _e('Taking tests (with or without specified results) can automatically add user to the group', 'qbuddy');?></li>
		<li><?php _e('A test completion (with or without specified results) may be required to allow joining or even viewing a group', 'qbuddy');?></li>
		<li><?php _e('Tests can be made private to selected BuddyPress groups', 'qbuddy');?></li>
		<li><?php _e('Tests can be streamed into the group feed.', 'qbuddy');?></li>
	</ul>
	
	<h3><?php _e('Integrate With Quiz Plugin:', 'qbuddy');?></h3>
	<form method="post">
	<ul>		
		<li><input type="radio" name="integration_mode" value="watu" <?php if($integration_mode == 'watu') echo 'checked'?>> <?php printf(__('Integrate with <a href="%s" target="_blank">Watu</a>', 'qbuddy'), 'https://wordpress.org/plugins/watu/');?></li>
		<li><input type="radio" name="integration_mode" value="watupro" <?php if($integration_mode == 'watupro') echo 'checked'?>> <?php printf(__('Integrate with <a href="%s" target="_blank">WatuPRO</a>', 'qbuddy'), 'https://calendarscripts.info/watupro/');?></li>
		<li><input type="radio" name="integration_mode" value="chained_quiz" <?php if($integration_mode == 'chained_quiz') echo 'checked'?>> <?php printf(__('Integrate with <a href="%s" target="_blank">Chained Quiz</a>', 'qbuddy'), 'https://wordpress.org/plugins/chained-quiz/');?></li>
	</ul>
	
	<p><?php printf(__('Not sure how to integrate? See <a href="%s" target="_blank">here</a>.', 'qbuddy'), 'https://wordpress.org/plugins/quizzes-for-buddypress/');?></p>
	
	<p><input type="submit" name="save_options" value="<?php _e('Save Options', 'qbuddy');?>"></p>
	<?php wp_nonce_field('qbuddy_options');?>
	</form>
	
</div>

<script type="text/javascript" >

</script>