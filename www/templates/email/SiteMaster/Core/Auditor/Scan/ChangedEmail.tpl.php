<?php
/**
 * @var $context \SiteMaster\Core\Auditor\Scan\CompletedEmail
 * @var $site \SiteMaster\Core\Registry\Site
 */
$site           = $context->scan->getSite();
$previous_scan  = $context->scan->getPreviousScan();
$site_pass_fail = $context->scan->isPassFail();
?>
<p>
    Hello, Fellow Web Developer!
</p>
<p>
    <?php echo \SiteMaster\Core\Config::get('SITE_TITLE') ?> has a <a href="<?php echo $site->getURL();?>">new report</a> for your site <?php echo $site->base_url ?>.
</p>
<?php
    $owner = 'None';
    $primary = 'None';
    $secondary = 'None';

    $owner_members = $site->getMembersWithRoleName('Owner');
    if (count($owner_members) > 0) {
        $owner_members->rewind();
        $owner_user = $owner_members->current()->getUser();
        $owner = $owner_user->first_name . " " . $owner_user->last_name;
    }

    $primary_members = $site->getMembersWithRoleName('Primary Site Manager');
    if (count($primary_members) > 0) {
        $primary_members->rewind();
        $primary_user = $primary_members->current()->getUser();
        $primary = $primary_user->first_name . " " . $primary_user->last_name;
    }

    $secondary_members = $site->getMembersWithRoleName('Secondary Site Manager');
    if (count($secondary_members) > 0) {
        $secondary_members->rewind();
        $secondary_user = $secondary_members->current()->getUser();
        $secondary = $secondary_user->first_name . " " . $secondary_user->last_name;
    }
?>

<?php if ($owner === 'None' || $primary === 'None' || $secondary === 'None'): ?>
    <p>
        Please assign users to the Owner, Primary Site Manager, and Secondary Site Manager roles.
        (<a href="<?php echo $site->getURL() ?>members/">Edit roles</a>)
    </p>
<?php endif; ?>

<?php
$arrow = "&#8596; (same)";
if ($previous_scan) {
    if ($previous_scan->gpa > $context->scan->gpa) {
        $arrow = "&#8595; (worse)";
    } else if ($previous_scan->gpa < $context->scan->gpa) {
        $arrow = "&#8593; (better)";
    }

    if ($site_pass_fail != $previous_scan->isPassFail()) {
        $arrow = "&#8800; <span class='secondary'>(incomparable)</span>";
    }
}
?>

<?php
if ($site_pass_fail) {
    ?>
    <table class="dcf-table" cellpadding="5" width="100%" style="border:1px solid #dddddd; margin-bottom: 1em;">
        <tr style="border-bottom:1px solid #dddddd">
            <th align="center">Current Site Status</th>
        </tr>
        <tr>
            <td align="center">
                <?php
                if ($context->scan->gpa == 100) {
                    echo 'Looks Good';
                } else {
                    echo 'Needs Work';
                }
                ?>
            </td>
        </tr>
    </table>
    <?php
}
?>

<table class="dcf-table" cellpadding="5" width="100%" style="border:1px solid #dddddd">
    <tr style="border-bottom:1px solid #dddddd">
        <?php 
        if ($site_pass_fail) {
            ?>
            <th align="center">Before</th>
            <th align="center">Change in Passing Pages</th>
            <th align="center">After</th>
            <?php
        } else {
            ?>
            <th align="center">Old GPA</th>
            <th align="center">Change</th>
            <th align="center">New GPA</th>
            <?php
        }
        ?>
    </tr>
    <tr>
        <td align="center"><?php echo ($previous_scan)?$previous_scan->gpa:'new site' ?></td>
        <td align="center"><?php echo $arrow ?></td>
        <td align="center">
            <?php 
            echo $context->scan->gpa;
            
            if ($site_pass_fail) {
                echo '%';
            }
            ?>
        </td>
    </tr>
</table>

<?php 
if ($previous_scan &&  $context->scan->gpa == $previous_scan->gpa) {
    ?>
    <p>
        There were some changes that did not affect the overall grade.  Please view the report from the URL above to find out exactly what changed.
    </p>
    <?php
}
?>

<p>
     The audit tool is designed to help you ensure the best experience for your users, and to mitigate risk to the university, by showing you potential problems — problems you can fix. Please view the report from the URL above; it’ll pinpoint what the problem(s) are, and provide some guidance on how to fix them.
</p>

<p>
    Thank you,<br />
    <?php echo \SiteMaster\Core\Config::get('EMAIL_SIGNATURE') ?>
</p>

<p>
    ps. This is an automated email sent by <a href="<?php echo \SiteMaster\Core\Config::get('URL') ?>"><?php echo \SiteMaster\Core\Config::get('SITE_TITLE') ?></a>. The system sends these emails because it detected that something has changed on your site. You received this email because you are a member of the site. You can remove yourself from the site by visiting: <a href="<?php echo $site->getURL();?>"><?php echo $site->getURL();?></a>
</p>
