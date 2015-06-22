<?php
use SiteMaster\Core\Config;

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
