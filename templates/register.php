<?php
\OCP\Util::addStyle('registration', 'style');
if ($_['entered']): ?>
	<?php if (empty($_['errormsg'])): ?>
		<ul class="success">
			<li>
			<?php print_unescaped($l->t('Thank you for registering, you should receive verification link in a few minutes.')); ?>
			</li>
		</ul>
	<?php else: ?>
		<form action="<?php print_unescaped(OC_Helper::linkToRoute('registration.register.validateEmail')) ?>" method="post">
			<fieldset>
				<ul class="error">
					<li><?php print_unescaped($_['errormsg']); ?></li>
				</ul>
				<p class="groupofone">
					<input type="email" name="email" id="email" placeholder="<?php print_unescaped($l->t('Email')); ?>" value="" required autofocus />
					<label for="email" class="infield"><?php print_unescaped($l->t( 'Email' )); ?></label>
					<img id="email-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
				</p>
				<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" />
				<input type="submit" id="submit" value="<?php print_unescaped($l->t('Request verification link')); ?>" />
			</fieldset>
		</form>
	<?php endif; ?>
<?php else: ?>
	<form action="<?php print_unescaped(OC_Helper::linkToRoute('registration.register.validateEmail')) ?>" method="post">
		<fieldset>
			<?php if ($_['errormsg']): ?>
				<ul class="error">
					<li><?php print_unescaped($_['errormsg']); ?></li>
					<li><?php print_unescaped($l->t('Please re-enter a valid email address')); ?></li>
				</ul>
			<?php else: ?>
				<ul class="msg">
					<li><?php print_unescaped($l->t('You will receive an email with verification link')); ?></li>
				</ul>
			<?php endif; ?>
			<p class="groupofone">
			<input type="email" name="email" id="email" placeholder="<?php print_unescaped($l->t('Email')); ?>" value="" required autofocus />
				<label for="email" class="infield"><?php print_unescaped($l->t('Email')); ?></label>
				<img id="email-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
			</p>
			<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']); ?>" />
			<input type="submit" id="submit" value="<?php print_unescaped($l->t('Request verification link')); ?>" />
		</fieldset>
	</form>
<?php endif; ?>
