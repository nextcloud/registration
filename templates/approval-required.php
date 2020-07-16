<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
style('registration', 'style');
?>
<div class="error">
	<h2><?php p($l->t('Approval required')) ?></h2>
	<ul>
		<li>
			<p><?php p($l->t('Your account has been successfully created, but it still needs approval from an administrator.')) ?></p>
		</li>
	</ul>
</div>
