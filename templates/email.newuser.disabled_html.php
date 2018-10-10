<?php
echo $l->t('A new user "%s" has created an account on %s and awaits admin approbation', [$_['user'], $_['sitename']]);
echo str_replace('{link}', $_['link'], '<br/><br/><a href="{link}">{link}</a>');
