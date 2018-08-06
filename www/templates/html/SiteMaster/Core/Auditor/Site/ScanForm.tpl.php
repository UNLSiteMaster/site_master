<?php
if ($context->canEdit()) {
    ?>
    <form action="<?php echo $context->getEditURL(); ?>" method="POST">
        <input type="hidden" name="action" value="scan" />
        <?php $csrf_helper->insertToken(\SiteMaster\Core\Controller::urlToRequestURI($context->getEditURL())) ?>
        <button type="submit" class="scan-site">Start a New Site Scan</button>
    </form>
    <?php
}
?>
