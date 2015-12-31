<?php
\OCP\Util::addStyle('registration', 'style');
?>
<form action="<?php print_unescaped(\OC::$server->getURLGenerator()->linkToRoute('registration.register.validateEmail')) ?>" method="post">
	<input type="hidden" name="requesttoken" value="<?php p($_['requesttoken']) ?>" />
	<fieldset>
		<?php if ( $_['errormsgs'] ) {?>
		<ul class="error">
			<?php foreach ( $_['errormsgs'] as $errormsg ) {
				echo "<li>$errormsg</li>";
			} ?>
		</ul>
		<?php } ?>
		<p class="grouptop">
		<input type="email" name="email" id="email" value="<?php echo $_['email']; ?>" placeholder="<?php print_unescaped($l->t('Email')); ?>" />
		<label for="email" class="infield"><?php echo $_['email']; ?></label>
		<img id="email-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
		</p>

		<p class="groupmiddle">
		<input type="text" name="username" id="username" value="<?php echo $_['entered_data']['user']; ?>" placeholder="<?php print_unescaped($l->t('Username')); ?>" />
		<label for="username" class="infield"><?php print_unescaped($l->t('Username')); ?></label>
		<img id="username-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/user.svg')); ?>" alt=""/>
		</p>

		<p class="groupmiddle">
		<input type="text" name="display_name" id="display_name" value="<?php echo $_['entered_data']['display_name']; ?>" placeholder="<?php print_unescaped($l->t('Display Name')); ?>" />
		<label for="display_name" class="infield"><?php print_unescaped($l->t('Display Name')); ?></label>
		<img id="rename-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/rename.svg')); ?>" alt=""/>
		</p>

		<p class="groupbottom">
		<input type="password" name="password" id="password" placeholder="<?php print_unescaped($l->t('Password')); ?>"/>
		<label for="password" class="infield"><?php print_unescaped($l->t( 'Password' )); ?></label>
		<img id="password-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/password.svg')); ?>" alt=""/>
		<input id="show" name="show" type="checkbox">
		<label style="display: inline;" for="show"></label>
		</p>
		<input type="submit" id="submit" value="<?php print_unescaped($l->t('Sign up')); ?>" />
	</fieldset>
</form>
