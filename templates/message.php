<?php if ( $_['errors'] ) {
	echo '<ul class="error">';
	foreach ( $_['errors'] as $error ) { ?>
		<li><?php echo $error; ?></li>
	<?php }
	echo '</ul>';
}
if ( $_['messages'] ) {
	echo '<ul class="success">';
	foreach ($_['messages'] as $message ) {?>
		<li><?php echo $message; ?></li>
<?php	}
	echo '</ul>';
}?>
