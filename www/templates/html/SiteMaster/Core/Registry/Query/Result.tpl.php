<?php
foreach ($context as $site) {
    echo $savvy->render($site, 'SiteMaster/Core/Registry/Site/Summary.tpl.php');
}
