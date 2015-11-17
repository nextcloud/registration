<?php
echo $l->t('To create a new account on %s, just click the following link:', [$_['sitename']]);
echo str_replace('{link}', $_['link'], '<br/><br/><a href="{link}">{link}</a>');
