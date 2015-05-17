<?php
script('registration', 'settings');
?>
<form id="registration" class="section">
	<h2><?php p($l->t('Registration')); ?></h2>
	<p>
	<label for="registered_user_group"><?php p($l->t('Default group that all registered users belong')); ?></label>
	<select id="registered_user_group" name="registered_user_group">
		<option value="none"><?php p($l->t('None')); ?></option>
<?php
foreach ( $_['groups'] as $group ) {
	echo '<option value="'.$group.'">'.$group.'</option>';
}
?>
	</select>
	</p>
</form>
