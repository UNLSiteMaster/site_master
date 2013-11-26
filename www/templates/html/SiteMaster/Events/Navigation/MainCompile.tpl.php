<?php
foreach ($context->getNavigation() as $url=>$title) {
    $active = '';
    if ($app->options['current_url'] == $url) {
        $active = 'class="active"';
    }
    ?>
    <li <?php echo $active?>>
        <a href="<?php echo $url;?>"><?php echo $title ?></a>
    </li>
<?php
}
?>
