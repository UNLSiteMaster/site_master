<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <div class="panel">
        <p>
            You will need to verify your membership before your roles are active.  You have a few options:
            <ul>
                <li>Upload a unique file to prove that you have physical access to the site</li>
                <li>Have verified member of this site manually verify your membership</li>
                <li>Have verified member of this site manually verify your individual roles</li>
            </ul>
        </p>
        
        <p>
            To manually verify yourself, you will need to create this file on your site:
        </p>
        <code>
            <?php echo $context->getVerificationURL(); ?>
        </code>
        <p>
            The file does not have to contain anything.  The only requirement is that the file exists.
        </p>
    </div>
    <input type="hidden" name="type" value="manual" />
    <input type="submit" value="Manually Verify Now" />
</form>
<?php
if ($context->canBypassManualVerification()) {
    ?>
    <form action="<?php echo $context->getEditURL(); ?>" method="POST">
        <div class="panel">
            <p>
                You can bypass the manual verification because you are already verified for this site.
            </p>
        </div>
        <input type="hidden" name="type" value="bypass" />
        <input type="submit" value="Skip Manual Verification and Verify Now" />
    </form>
    <?php
}
?>