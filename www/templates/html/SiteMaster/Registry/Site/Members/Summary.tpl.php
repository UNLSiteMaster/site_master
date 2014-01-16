<?php
if ($context->count()) {
    ?>
    <ul>
    <?php 
    foreach ($context as $member) {
        echo $savvy->renderWithTheme($member, 'SiteMaster/Registry/Site/Member/Summary.tpl.php');
    }
    ?>
    </ul>
    <?php
}