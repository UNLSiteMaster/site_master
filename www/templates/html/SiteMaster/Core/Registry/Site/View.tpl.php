
<?php
if ($scan = $context->site->getLatestScan()) {
    echo $savvy->render($scan);
} else {
    ?>
    <p>
        No scans found
    </p>
    <?php
}
