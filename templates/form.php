<form action="<?php print_unescaped(OC_Helper::linkToRoute('registration.register.createAccount')) ?>" method="post">
	<fieldset>
		<?php if ( $_['errormsgs'] ) {?>
		<ul class="error">
			<?php foreach ( $_['errormsgs'] as $errormsg ) {
				echo "<li>$errormsg</li>";
			} ?>
		</ul>
		<?php } ?>
		<p class="infield grouptop">
		<input type="email" name="email" id="email" value="<?php echo $_['email']; ?>" disabled />
		<label for="email" class="infield"><?php echo $_['email']; ?></label>
		<img class="svg" src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>" alt=""/>
		</p>

		<p class="infield groupmiddle">
		<input type="text" name="username" id="username" value="<?php echo $_['entered_data']['user']; ?>" />
		<label for="username" class="infield"><?php print_unescaped($l->t( 'Username' )); ?></label>
		<img class="svg" src="<?php print_unescaped(image_path('', 'actions/user.svg')); ?>" alt=""/>
		</p>

		<p class="infield groupbottom">
		<input type="password" name="password" id="password" />
		<label for="password" class="infield"><?php print_unescaped($l->t( 'Password' )); ?></label>
		<img id="password-icon" class="svg" src="<?php print_unescaped(image_path('', 'actions/password.svg')); ?>" alt=""/>
		<input id="show" name="show" type="checkbox">
		<label style="display: inline;" for="show"></label>
		</p>
		<input type="submit" id="submit" value="<?php print_unescaped($l->t('Create account')); ?>" />
	</fieldset>
</form>
