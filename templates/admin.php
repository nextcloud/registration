<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
script('registration', 'settings');
style('registration', 'settings');
?>
<form id="registration_settings_form" class="section">
	<h2><?php p($l->t('Registration')); ?></h2><span id="registration_settings_msg" class="msg"></span>

	<h3><?php p($l->t('Registered users default group')); ?></h3>
	<p>
		<label>
			<select id="registered_user_group" name="registered_user_group">
				<option value="none" <?php echo $_['current'] === 'none' ? 'selected="selected"' : ''; ?>><?php p($l->t('None')); ?></option>
<?php
foreach ($_['groups'] as $group) {
	$selected = $_['current'] === $group ? 'selected="selected"' : '';
	echo '<option value="'.$group.'" '.$selected.'>'.$group.'</option>';
}
?>
			</select>
		</label>
	</p>

	<h3><?php p($l->t('Allowed email domains')); ?></h3>
	<p>
		<label>
			<input type="text" id="allowed_domains" name="allowed_domains" value="<?php p($_['allowed']);?>" placeholder="nextcloud.com;*.example.com">
		</label>
	</p>
	<em><?php p($l->t('Enter a semicolon-separated list of allowed email domains, * for wildcard. Example: %s', ['nextcloud.com;*.example.com']));?></em>

	<p>
		<input type="checkbox" id="domains_is_blocklist" class="checkbox" name="domains_is_blocklist" <?php if ($_['domains_is_blocklist'] === 'yes') {
	echo ' checked';
} ?>>
		<label for="domains_is_blocklist"><?php p($l->t('Block listed email domains instead of allowing them')); ?></label>
	</p>

	<p>
		<input type="checkbox" id="show_domains" class="checkbox" name="show_domains" <?php if ($_['show_domains'] === 'yes') {
	echo ' checked';
} ?>>
		<label for="show_domains"><?php p($l->t('Show the allowed/blocked email domains to users')); ?></label>
	</p>

	<p>
		<input type="checkbox" id="email_is_login" class="checkbox" name="email_is_login" <?php if ($_['email_is_login'] === 'yes') {
	echo ' checked';
} ?>>
		<label for="email_is_login"><?php p($l->t('Force email as login name')); ?></label>
	</p>

	<h3><?php p($l->t('Admin approval')); ?></h3>
	<p>
		<input type="checkbox" id="admin_approval_required" class="checkbox" name="admin_approval_required" <?php if ($_['approval_required'] === 'yes') {
	echo ' checked';
} ?>>
		<label for="admin_approval_required"><?php p($l->t('Require admin approval')); ?></label>
	</p>

	<em><?php p($l->t('Enabling "admin approval" will prevent registrations from mobile and desktop clients to complete as the credentials can not be verified by the client until the user was enabled.'));?></em>
</form>
