<?php
if ($context->canEdit()) {
    ?>
    <form action="<?php echo $context->getEditURL(); ?>" method="POST">
        <input type="hidden" name="action" value="scan" />
        <button type="submit" class="scan-site">Schedule a Site Scan</button>
    </form>
    <?php
}
?>
