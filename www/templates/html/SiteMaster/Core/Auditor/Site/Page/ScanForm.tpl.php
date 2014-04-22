<?php
if ($context->canEdit()) {
    ?>
    <form action="<?php echo $context->getEditURL(); ?>" method="POST">
        <input type="hidden" name="action" value="scan" />
        <button type="submit" class="scan-page">Start a New Page Scan</button>
    </form>
<?php
}
?>
