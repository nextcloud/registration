<?php
echo $l->t("To create a new account on ownCloud, just click the following link:");
echo str_replace('{link}', $_['link'], "\n\n{link}");
