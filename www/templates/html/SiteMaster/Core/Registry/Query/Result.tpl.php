<?php
if ($context->getInnerIterator()->count() == 0) {
    ?>
    Sorry, no sites could be found
    <?php
} else {
    foreach ($context as $site) {
        echo $savvy->render($site, 'SiteMaster/Core/Registry/Site/Summary.tpl.php');
    }
}
