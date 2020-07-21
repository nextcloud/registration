<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
script('registration', 'settings');
?>
<form id="registration_settings_form" class="section">
	<h2><?php p($l->t('Registration')); ?></h2><span id="registration_settings_msg" class="msg"></span>
	<p>
	<label for="registered_user_group"><?php p($l->t('Default group that all registered users belong')); ?></label>
	<select id="registered_user_group" name="registered_user_group">
		<option value="none" <?php echo $_['current'] === 'none' ? 'selected="selected"' : ''; ?>><?php p($l->t('None')); ?></option>
<?php
foreach ($_['groups'] as $group) {
	$selected = $_['current'] === $group ? 'selected="selected"' : '';
	echo '<option value="'.$group.'" '.$selected.'>'.$group.'</option>';
}
?>
	</select>
	</p>
	<p>
	<label for="allowed_domains"><?php p($l->t('Allowed mail address domains for registration')); ?></label>
	<input type="text" id="allowed_domains" name="allowed_domains" value=<?php p($_['allowed']);?>>
	</p>
	<p>
	<em><?php p($l->t('Enter a semicolon-separated list of allowed domains. Example: nextcloud.com;example.com'));?></em>
	</p>

	<div style="margin-top: 10px;">
		<p>
			<input type="checkbox" id="admin_approval_required" class="checkbox" name="admin_approval_required" <?php if ($_['approval_required'] === "yes") {
	echo " checked";
} ?>>
			<label for="admin_approval_required"><?php p($l->t('Require admin approval?')); ?></label>
		</p>

		<em><?php p($l->t('Enabling "admin approval" will prevent registrations from mobile and desktop clients to complete as the credentials can not be verified by the client until the user was enabled.'));?></em>
	</div>
</form>
