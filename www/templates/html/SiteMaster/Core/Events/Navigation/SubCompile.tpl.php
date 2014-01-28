<?php

/**
 * @var $context \SiteMaster\Core\Events\Navigation\SubCompile
 */
$nav = $context->getNavigation();

if (count($nav)) {
    foreach ($nav as $url=>$title)
    ?>
    
    <ul class="dropdown">
        <li>
            <a href="<?php echo $url;?>"><?php echo $title ?></a>
        </li>
    </ul>
    <?php
}

