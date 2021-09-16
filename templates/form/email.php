<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
style('registration', 'style');
script('registration', 'registration-form');
session_start();
$captcha = "";
if (isset($_SESSION["captcha"]) && $_SESSION["captcha"] != "") {
	$captcha = $_SESSION["captcha"];
} else {
	$captcha = rand(1000, 9999);
	$_SESSION["captcha"] = $captcha;
}

?>
<form action="" method="post">
	<fieldset>
		<?php if ($_['message']): ?>
			<ul class="error">
				<li><?php p($_['message']); ?></li>
			</ul>
		<?php endif; ?>

		<p class="groupofone">
			<input type="email" name="email" id="email"
				   placeholder="<?php p($l->t('Email')); ?>"
				   value="<?php p($_['email']); ?>" required autofocus/>
			<label for="email"
				   class="infield"><?php p($l->t('Email')); ?></label>
			<img id="email-icon" class="svg"
				 src="<?php print_unescaped(image_path('', 'actions/mail.svg')); ?>"
				 alt=""/>
		</p>

		<div id="terms_of_service"></div>

		<?php if ($_['admin_registe_captcha'] === "yes"): ?>
			<p class="cpt-p">
				<label
					class="cpt-label"><?php p($l->t('Captcha')); ?><?php echo $captcha; ?></label>
				<br>
				<input type="text" name="captcha" value=""
					   placeholder="Please enter captcha">
			</p>
		<?php endif; ?>

		<input type="hidden" name="requesttoken"
			   value="<?php p($_['requesttoken']); ?>"/>
		<input type="submit" id="submit" value="<?php
		if ($_['disable_email_verification'] === 'yes') {
			p($l->t('Continue'));
		} elseif ($_['is_login_flow']) {
			p($l->t('Request verification code'));
		} else {
			p($l->t('Request verification link'));
		} ?>"/>

		<a id="lost-password-back"
		   href="<?php print_unescaped(\OC::$server->getURLGenerator()->linkToRoute('core.login.showLoginForm')) ?>">
			<?php p($l->t('Back to login')); ?>
		</a>
	</fieldset>
</form>
