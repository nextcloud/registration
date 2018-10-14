<?php
\OCP\Util::addStyle('registration', 'style');
if ( \OCP\Util::getVersion()[0] >= 12 )
        \OCP\Util::addStyle('core', 'guest')
?>
<ul class="msg error-wide nc-theming-main-text">
	<li><?php print_unescaped($_['msg'])?></li>
</ul>
