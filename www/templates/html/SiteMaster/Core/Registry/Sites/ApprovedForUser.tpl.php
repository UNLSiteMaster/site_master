<?php
if (!$context->count()) {
    ?>
    You currently have no approved sites
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