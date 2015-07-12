<?php
script('registration', 'settings');
?>
<form id="registration" class="section">
	<h2><?php p($l->t('Registration')); ?></h2>
	<p>
	<label for="registered_user_group"><?php p($l->t('Default group that all registered users belong')); ?></label>
	<select id="registered_user_group" name="registered_user_group">
		<option value="none" <?php echo $_['current'] === 'none' ? 'selected="selected"' : ''; ?>><?php p($l->t('None')); ?></option>
<?php
foreach ( $_['groups'] as $group ) {
	$selected = $_['current'] === $group ? 'selected="selected"' : '';
	echo '<option value="'.$group.'" '.$selected.'>'.$group.'</option>';
}
?>
	</select>
	</p>
	<p>
	<label for="allowed_domains"><?php p($l->t('Domain from which registrations are allowed')); ?></label>
	<input type="text"   id="allowed_domains" name="allowed_domains" value=<?php p($l->t('allowed'));?>>
	<button class="save"><?php p($l->t('Save'));?></button>

</form>
	</p>
