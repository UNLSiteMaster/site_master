<?php
/**
 * @var $context \SiteMaster\Core\Auditor\Scan\CompletedEmail
 * @var $site \SiteMaster\Core\Registry\Site
 */
$site = $context->scan->getSite();

$previous_scan = $context->scan->getPreviousScan();
?>
<p>
    Hello, Fellow Web Developer!
</p>
<p>
    <?php echo \SiteMaster\Core\Config::get('SITE_TITLE') ?> has a new report ready for you to view at <?php echo $site->getURL();?> for your site <?php echo $site->base_url ?>.
</p>

<?php
$arrow = "&#8596; (same)";
if ($previous_scan) {
    if ($previous_scan->gpa > $context->scan->gpa) {
        $arrow = "&#8595; (worse)";
    } else if ($previous_scan->gpa < $context->scan->gpa) {
        $arrow = "&#8593; (better)";
    }
}
?>

<table cellpadding="5" width="100%" style="border:1px solid #dddddd">
    <tr style="border-bottom:1px solid #dddddd">
        <th align="center">Old GPA</th>
        <th align="center">Change</th>
        <th align="center">New GPA</th>
    </tr>
    <tr>
        <td align="center"><?php echo ($previous_scan)?$previous_scan->gpa:'new site' ?></td>
        <td align="center"><?php echo $arrow ?></td>
        <td align="center"><?php echo $context->scan->gpa;?></td>
    </tr>
</table>

<p>
     The audit tool is designed to help you ensure the best experience for your users, and to mitigate risk to the university, by showing you potential problems — problems you can fix. Please view the report from the URL above; it’ll pinpoint what the problem(s) are, and provide some guidance on how to fix them.
</p>

<p>
    Thank you,<br />
    <?php echo \SiteMaster\Core\Config::get('EMAIL_SIGNATURE') ?>
</p>

<p>
    ps. This is an automated email sent by <?php echo \SiteMaster\Core\Config::get('URL') ?>. The system sends these emails because it detected that something has changed on your site. You received this email because you are a member of the site. You can remove yourself from the site by visiting: <?php echo $site->getURL();?>
</p>
