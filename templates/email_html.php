<?php
// TODO use OC_Default to get site name
echo $l->t('To create a new account on ownCloud, just click the following link:');
echo str_replace('{link}', $_['link'], '<br/><br/><a href="{link}">{link}</a>');
