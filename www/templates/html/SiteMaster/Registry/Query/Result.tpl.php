<?php
foreach ($context as $site) {
    echo $savvy->render($site, 'SiteMaster/Registry/Site/Summary.tpl.php');
}
