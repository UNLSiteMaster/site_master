
<ul>
<?php
foreach ($context as $site) {
    ?>
    <li><?php echo $site->base_url; ?></li>
    <?php
}
?>
</ul>