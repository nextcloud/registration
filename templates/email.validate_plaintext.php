<?php
echo $l->t("To create a new account on %s, just click the following link:", [$_['sitename']]);
echo str_replace('{link}', $_['link'], "\n\n{link}");
