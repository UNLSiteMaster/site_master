<div>
    <h2>Sites that I have approved roles for</h2>
    <?php
    echo $savvy->render($context->user->getApprovedSites());
    ?>
</div>

<div>
    <h2>Sites where my membership is pending approval</h2>
    <?php
    echo $savvy->render($context->user->getPendingSites());
    ?>
</div>