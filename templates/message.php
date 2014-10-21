<ul class="error-wide">
	<?php foreach($_["msgs"] as $msg):?>
		<li class='msg'>
			<?php print_unesacped($msg) ?><br/>
		</li>
	<?php endforeach ?>
</ul>
