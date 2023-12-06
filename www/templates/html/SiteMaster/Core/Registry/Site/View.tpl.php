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
                    <a href="<?php echo $context->site->getURL() . 'verify/' ?>" class="button dcf-btn">Verify Me Now</a>
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
?>

<?php
    $owner = 'None';
    $primary = 'None';

    $owner_members = $context->site->getMembersWithRoleName('Owner');
    if (count($owner_members) > 0) {
        $owner_members->rewind();
        $owner_user = $owner_members->current()->getUser();
        $owner = $owner_user->first_name . " " . $owner_user->last_name;
    }

    $primary_members = $context->site->getMembersWithRoleName('Primary Site Manager');
    if (count($primary_members) > 0) {
        $primary_members->rewind();
        $primary_user = $primary_members->current()->getUser();
        $primary = $primary_user->first_name . " " . $primary_user->last_name;
    }
?>

<?php if ($owner === 'None' || $primary === 'None'): ?>
    <div class="dcf-mt-6">
        <div class="dcf-notice dcf-notice-warning" hidden>
            <h2>Your site is missing important roles</h2>
            <div>
                Please assign users to the Owner, Primary Site Manager, and Secondary Site Manager roles.
                <?php if ($context->site->isCurrentUserAdmin()): ?>
                    (<a href="<?php echo $context->site->getURL() ?>members/">Edit roles</a>)
                <?php else: ?>
                    (<a href="<?php echo $context->site->getURL() ?>join/">Edit my roles</a>)
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
    echo $savvy->render($context, 'SiteMaster/Core/Registry/Site/history-graph.tpl.php');
?>

<div class="scan-include">
    <?php
    if ($scan = $context->getScan()) {

      $savvy->loadScriptDeclaration('
            var request = $.ajax("' . $scan->getURL() . '?format=partial");
            request.done(function(html) {
                $("#scan_ajax").html(html);
                sitemaster.initAnchors();
                sitemaster.initTables();
            });
            request.fail(function(jqXHR, textStatus) {
                $("#scan_ajax").html("Request failed... please reload the page");
            });
      ');
        ?>
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
