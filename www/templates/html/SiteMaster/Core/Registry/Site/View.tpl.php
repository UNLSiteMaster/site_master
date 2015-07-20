<?php
use SiteMaster\Core\Config;

if ($user && $membership = $context->site->getMembershipForUser($user->getRawObject())) {
    $display_notice = false;
    $needs_verification = $membership->needsVerification();
    $unapproved = false;
    
    $roles = $membership->getRoles();
    foreach ($roles as $role) {
        if (!$role->isApproved()) {
            $display_notice = true;
            $unapproved = true;
        }
    }
    
    if ($needs_verification || $unapproved) {
        ?>
        <div class="notice">
            <h2>
                It looks like you have some pending roles
            </h2>
            <p>
                <?php
                if ($needs_verification): ?>
                    You need to verify yourself or ask another member with the 'admin' role to approve your membership.
                    <a href="<?php echo $context->site->getURL() . 'verify/' ?>" class="button wdn-button">Verify Me Now</a>
                <?php endif; ?>
                <?php if ($unapproved): ?>
                    You will need to ask a member with the 'admin' role to approve your pending role(s).
                    
                    <?php
                    $admins = $context->site->getMembersWithRoleName('admin');
                    
                    if ($admins && $admins->count()): ?>
                        These people are able to approve your role:
                        <ul>
                            <?php foreach ($admins as $admin): ?>
                            <li><?php echo $admin->getUser()->getName(); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        There are currently no admins for this site.  You will either have to make yourself an admin or wait for an admin to join.
                    <?php endif; ?>
                <?php endif; ?>
            </p>
        </div>
        <?php
    }
}

echo $savvy->render($context, 'SiteMaster/Core/Registry/Site/history-graph.tpl.php');
?>

<div class="scan-include">
    <?php
    if ($scan = $context->getScan()) {
        ?>
        <script type="text/javascript">
            var request = $.ajax("<?php echo $scan->getURL() ?>?format=partial");
            request.done(function(html) {
                $("#scan_ajax").html(html);
                sitemaster.initAnchors();
                sitemaster.initInPageNav();
                sitemaster.initTables();
            });
            request.fail(function(jqXHR, textStatus) {
                $("#scan_ajax").html("Request failed... please reload the page");
            });
        </script>
        <div id="scan_ajax">
            <img alt="loading..." src="<?php echo $base_url . 'www/images/loading.gif' ?>" />
            <p>
                Please wait while we load the latest scan.  This should be pretty quick.
            </p>
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
