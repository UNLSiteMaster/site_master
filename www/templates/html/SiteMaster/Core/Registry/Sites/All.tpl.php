<?php
if (!$context->count()) {
    ?>
    No sites
<?php
} else {
    ?>
    <ul>
        <?php
        foreach ($context as $site) {
            ?>
            <li><?php echo $site->getTitle() ?></li>
        <?php
        }
        ?>
    </ul>
<?php
}
