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


?>
<div class="scan-include">
    <?php
    if ($scan = $context->getScan()) {
        ?>
        <script type="text/javascript">
            var request = $.ajax("<?php echo $scan->getURL() ?>?format=partial");
            request.done(function(html) {
                $("#scan_ajax").html(html);
            });
            request.fail(function(jqXHR, textStatus) {
                $("#scan_ajax").html("Request failed... please reload the page");
            });
        </script>
        <div id="scan_ajax">
            Loading...
        </div>
        <?php
    } else {
        ?>
        <p>
            No scans found
        </p>
        <?php
    }
    ?>
</div>
