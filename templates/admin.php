<?php
script('registration', 'settings');
?>
<form id="registration_settings_form" class="section">
	<h2><?php p($l->t('Registration')); ?></h2><span id="registration_settings_msg" class="msg"></span>
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
	<label for="allowed_domains"><?php p($l->t('Allowed mail address domains for registration')); ?></label>
	<input type="text" id="allowed_domains" name="allowed_domains" value=<?php p($_['allowed']);?>>
	</p>
	<p>
	<em><?php p($l->t('Enter a semicolon-separated list of allowed domains. Example: owncloud.com;github.com'));?></em>
	</p>

	<p>

	<input class="checkbox" type="checkbox" id="admin_approval_required" name="admin_approval_required" <?php if($_['approval_required'] === "yes" ) echo " checked"; ?>>
	<label for="admin_approval_required"><?php p($l->t('Require admin approval?')); ?></label>
	</input>
	</p>

	<h3><?php p($l->t('Show extra fields in registration form?')); ?>
	</h3>

	<p>
	<input class="checkbox" type="checkbox" id="fullname" name="fullname" <?php if($_['fullname'] === "yes" ) echo " checked"; ?>>
	<label for="fullname"><?php p($l->t('Full name')); ?></label>
	</input>
	</p>

	<p>
	<input class="checkbox" type="checkbox" id="country" name="country" <?php if($_['country'] === "yes" ) echo " checked"; ?>>
	<label for="country"><?php p($l->t('Country')); ?></label>
	</input>
	</p>

	<p>
	<input class="checkbox" type="checkbox" id="language" name="language" <?php if($_['language'] === "yes" ) echo " checked"; ?>>
	<label for="language"><?php p($l->t('Language')); ?></label>
	</input>
	</p>

	<p>
	<input class="checkbox" type="checkbox" id="timezone" name="timezone" <?php if($_['timezone'] === "yes" ) echo " checked"; ?>>
	<label for="timezone"><?php p($l->t('Timezone')); ?></label>
	</input>
	</p>

	<p>
	<input class="checkbox" type="checkbox" id="company" name="company" <?php if($_['company'] === "yes" ) echo " checked"; ?>>
	<label for="company"><?php p($l->t('Company')); ?></label>
	</input>
	</p>

	<p>
	<input class="checkbox" type="checkbox" id="phoneno" name="phoneno" <?php if($_['phoneno'] === "yes" ) echo " checked"; ?>>
	<label for="phoneno"><?php p($l->t('Phone number')); ?></label>
	</input>
	</p>
</form>
