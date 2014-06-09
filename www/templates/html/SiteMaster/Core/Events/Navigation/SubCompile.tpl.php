<?php

/**
 * @var $context \SiteMaster\Core\Events\Navigation\SubCompile
 */
$nav = $context->getNavigation();

if (count($nav)) {
    ?>
    <ul class="dropdown">
        <?php
        foreach ($nav as $url=>$title) {
            ?>
            <li>
                <a href="<?php echo $url;?>"><?php echo $title ?></a>
            </li>
            <?php
        }
    ?>
    </ul>
<?php
}
