<?php
foreach ($context as $site) {
    echo $savvy->renderWithTheme($site, 'SiteMaster/Registry/Site/Summary.tpl.php');
}
