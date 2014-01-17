<?php
if ($context->count()) {
    ?>
    <ul>
    <?php 
    foreach ($context as $member) {
        echo $savvy->render($member, 'SiteMaster/Core/Registry/Site/Member/Summary.tpl.php');
    }
    ?>
    </ul>
    <?php
}