<?php
if ($user && $membership = $context->site->getMembershipForUser($user->getRawObject())) {
    $display_notice = false;
    $verified = $membership->isVerified();
    $unapproved = false;
    
    $roles = $membership->getRoles();
    foreach ($roles as $role) {
        if (!$role->isApproved()) {
            $display_notice = true;
            $unapproved = true;
        }
    }
    
    if (!$verified || $unapproved) {
        ?>
        <div class="notice">
            <h2>
                It looks like you are unverified or have unapproved roles.
            </h2>
            <p>
                <?php
                if (!$verified) {
                    ?>
                    <a href="<?php echo $context->site->getURL() . 'verify/' ?>" class="button wdn-button">Verify Me Now</a>
                <?php
                }
                ?>
                <?php
                if ($unapproved) {
                    ?>
                    <a href="<?php echo $context->site->getURL() . 'join/' ?>" class="button wdn-button">Edit My Roles</a>
                <?php
                }
                ?>
            </p>
        </div>
        <?php
    }
}



if ($scan = $context->site->getLatestScan()) {
    echo $savvy->render($scan);
} else {
    ?>
    <p>
        No scans found
    </p>
    <?php
}
