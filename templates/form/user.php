<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
style('registration', 'style');
script('registration', 'form');
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


		<p class="grouptop">
			<input type="email" name="email" id="email" value="<?php p($_['email']); ?>" disabled />
			<label for="email" class="infield"><?php p($_['email']); ?></label>
			<img id="email-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
		</p>

		<?php if (!$_['email_is_login']) { ?>
		<p class="groupmiddle">
			<input type="text" name="username" id="username" value="<?php if (!empty($_['entered_data']['user'])) {
	p($_['entered_data']['user']);
} ?>" placeholder="<?php p($l->t('Username')); ?>" />
			<label for="username" class="infield"><?php p($l->t('Username')); ?></label>
			<img id="username-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/user.svg')); ?>" alt=""/>
		</p>
		<?php } else { ?>
			<input type="hidden" name="username" value="<?php p($_['email']); ?>" />
		<?php } ?>

		<p class="groupbottom">
			<input type="password" name="password" id="password" placeholder="<?php p($l->t('Password')); ?>"/>
			<label for="password" class="infield"><?php p($l->t('Password')); ?></label>
			<img id="password-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/password.svg')); ?>" alt=""/>
			<a id="showadminpass" href="#" class="toggle-password">
				<img src="<?php print_unescaped(image_path('core', 'actions/toggle.svg')); ?>">
			</a>
		</p>
		<input type="submit" id="submit" value="<?php p($l->t('Create account')); ?>" />
	</fieldset>
</form>
