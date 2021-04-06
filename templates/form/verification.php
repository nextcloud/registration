<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
style('registration', 'style');
script('registration', 'registration-form');
?>
	<form action="" method="post">
		<fieldset>
			<?php if ($_['message']): ?>
				<ul class="error">
					<li><?php p($_['message']); ?></li>
				</ul>
			<?php endif; ?>

			<p class="groupofone">
			<input type="text" name="token" id="token" placeholder="<?php p($l->t('Verification code')); ?>" value="" required autofocus />
				<label for="token" class="infield"><?php p($l->t('Verification code')); ?></label>
				<img id="token-icon" class="svg" src="<?php print_unescaped(image_path('registration', 'verify.svg')); ?>" alt=""/>
			</p>
			<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" />
			<input type="submit" id="submit" value="<?php p($l->t('Verify')); ?>" />

			<a id="lost-password-back" href="<?php print_unescaped(\OC::$server->getURLGenerator()->linkToRoute('core.login.showLoginForm')) ?>">
				<?php p($l->t('Back to login')); ?>
			</a>
		</fieldset>
	</form>
