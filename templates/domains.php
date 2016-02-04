<?php
\OCP\Util::addStyle('registration', 'style');
?>
<ul class="error-wide">
	<li class='error'><?php p($l->t('Registration is only allowed for the following domains:')); ?>
	<?php
	foreach ($_['domains'] as $domain ){
		echo "<p class='hint'>";
		p($domain);
		echo "</p>";
	}
	?>
	</li>
</ul>
