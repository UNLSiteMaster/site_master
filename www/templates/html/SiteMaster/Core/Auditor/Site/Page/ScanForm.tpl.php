<?php
if ($context->canEdit()) {
    ?>
    <form class="dcf-form" action="<?php echo $context->getEditURL(); ?>" method="POST">
        <input type="hidden" name="action" value="scan" />
        <?php $csrf_helper->insertToken(\SiteMaster\Core\Controller::urlToRequestURI($context->getEditURL())) ?>
        <button type="submit" class="dcf-btn dcf-btn-primary scan-page">Rescan This Page</button>
    </form>
<?php
}
?>
