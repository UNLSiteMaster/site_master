<div>
    <h2>Approved Sites</h2>
    <?php
    echo $savvy->render($context->user->getApprovedSites());
    ?>
</div>

<div>
    <h2>Pending Sites</h2>
    <?php
    echo $savvy->render($context->user->getPendingSites());
    ?>
</div>