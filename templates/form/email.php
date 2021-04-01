<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
style('registration', 'style');
?>
	<form action="" method="post">
		<fieldset>
			<?php if ($_['message']): ?>
				<ul class="error">
					<li><?php p($_['message']); ?></li>
				</ul>
			<?php endif; ?>

			<p class="groupofone">
			<input type="email" name="email" id="email" placeholder="<?php p($l->t('Email')); ?>" value="<?php p($_['email']); ?>" required autofocus />
				<label for="email" class="infield"><?php p($l->t('Email')); ?></label>
				<img id="email-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
			</p>
			<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" />
			<input type="submit" id="submit" value="<?php
				if ($_['disable_email_verification'] === 'yes') {
					p($l->t('Continue'));
				} elseif ($_['is_login_flow']) {
					p($l->t('Request verification code'));
				} else {
					p($l->t('Request verification link'));
				} ?>" />

			<a id="lost-password-back" href="<?php print_unescaped(\OC::$server->getURLGenerator()->linkToRoute('core.login.showLoginForm')) ?>">
				<?php p($l->t('Back to login')); ?>
			</a>
		</fieldset>
	</form>
