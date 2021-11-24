<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
style('registration', 'style');
script('registration', 'registration-form');
?><form action="" method="post">
	<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>" />
	<fieldset>
		<?php if (!empty($_['message'])) {?>
		<ul class="error">
			<li><?php p($_['message']); ?></li>
		</ul>
		<?php } else { ?>
		<ul class="msg">
			<li><?php p($l->t('Welcome, you can create your account below.'));?></li>
		</ul>
		<?php } ?>

		<?php if (!empty($_['additional_hint'])): ?>
			<ul class="msg">
				<li><?php p($_['additional_hint']); ?></li>
			</ul>
		<?php endif; ?>

		<?php if (!$_['email_is_optional'] || !empty($_['email'])) { ?>
			<p class="grouptop">
				<input type="email" name="email" id="email" value="<?php p($_['email']); ?>" disabled />
				<label for="email" class="infield"><?php p($_['email']); ?></label>
				<img id="email-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
			</p>
		<?php } ?>

		<?php if (!$_['email_is_login']) { ?>
			<p class="groupmiddle">
				<input type="text" name="loginname" id="loginname" value="<?php if (!empty($_['loginname'])) {
	p($_['loginname']);
} ?>" placeholder="<?php p($l->t('Login name')); ?>" required />
				<label for="loginname" class="infield"><?php p($l->t('Login name')); ?></label>
				<img id="loginname-icon" class="svg" src="<?php print_unescaped(image_path('', 'categories/auth.svg')); ?>" alt=""/>
			</p>
		<?php } else { ?>
			<input type="hidden" name="loginname" value="<?php p($_['email']); ?>" />
		<?php } ?>

		<?php if ($_['show_fullname']) { ?>
		<p class="groupmiddle">
			<input type="text" name="fullname" id="fullname" value="<?php if (!empty($_['fullname'])) {
	p($_['fullname']);
} ?>" placeholder="<?php p($l->t('Full name')); ?>" <?php if ($_['enforce_fullname']) {
	p('required');
} ?> />
			<label for="fullname" class="infield"><?php p($l->t('Full name')); ?></label>
			<img id="fullname-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/user.svg')); ?>" alt=""/>
		</p>
		<?php } else { ?>
			<input type="hidden" name="fullname" value="" />
		<?php } ?>

		<?php if ($_['show_phone']) { ?>
			<p class="groupmiddle">
				<input type="text" name="phone" id="phone" value="<?php if (!empty($_['phone'])) {
	p($_['phone']);
} ?>" placeholder="<?php p($l->t('Phone number')); ?>" <?php if ($_['enforce_phone']) {
	p('required');
} ?> />
				<label for="phone" class="infield"><?php p($l->t('Phone number')); ?></label>
				<img id="phone-icon" class="svg" src="<?php print_unescaped(image_path('', 'clients/phone.svg')); ?>" alt=""/>
			</p>
		<?php } else { ?>
			<input type="hidden" name="phone" value="" />
		<?php } ?>

		<p class="groupbottom">
			<input type="password" name="password" id="password" value="<?php if (!empty($_['password'])) {
	p($_['password']);
} ?>" placeholder="<?php p($l->t('Password')); ?>" required />
			<label for="password" class="infield"><?php p($l->t('Password')); ?></label>
			<img id="password-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/password.svg')); ?>" alt=""/>
			<a id="showadminpass" href="#" class="toggle-password">
				<img src="<?php print_unescaped(image_path('core', 'actions/toggle.svg')); ?>">
			</a>
		</p>
		<input type="submit" id="submit" value="<?php p($l->t('Create account')); ?>" />
	</fieldset>
</form>
