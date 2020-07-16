<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */
style('registration', 'style');
?>
<ul class="error-wide">
	<li class='error'><?php p($l->t('Registration is only allowed for the following domains:')); ?>
	<?php
	foreach ($_['domains'] as $domain) {
		echo "<p class='hint'>";
		p($domain);
		echo "</p>";
	}
	?>
	</li>
</ul>
