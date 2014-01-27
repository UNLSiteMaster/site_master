<?php
foreach ($context->getNavigation() as $url=>$title) {
    $active = 'class="has-dropdown"';
    if ($app->options['current_url'] == $url) {
        $active = 'class="active has-dropdown"';
    }
    ?>
    <li <?php echo $active?>>
        <a href="<?php echo $url;?>"><?php echo $title ?></a>
        <?php
        $subNav = \SiteMaster\Core\Plugin\PluginManager::getManager()->dispatchEvent(
            \SiteMaster\Core\Events\Navigation\SubCompile::EVENT_NAME,
            new \SiteMaster\Core\Events\Navigation\SubCompile($url)
        );

        echo $savvy->render($subNav);
        ?>
    </li>
<?php
}
?>
