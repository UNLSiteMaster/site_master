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
<p>
    The site’s current <?php echo \SiteMaster\Core\Config::get('SITE_TITLE') ?> GPA is <?php echo $context->scan->gpa;
    if ($previous_scan) {
        echo ', compared to the ' . $previous_scan->gpa . ' GPA reported the last time the site was checked.';
    } else {
        echo '.';
    }
    ?>
     The audit tool is designed to help you ensure the best experience for your users, and to mitigate risk to the university, by showing you potential problems — problems you can fix. Please view the report from the URL above; it’ll pinpoint what the problem(s) are, and provide some guidance on how to fix them.
</p>

<p>
    Thank you,<br />
    <?php echo \SiteMaster\Core\Config::get('EMAIL_SIGNATURE') ?>
</p>

<p>
    ps. This is an automated email sent by <?php echo \SiteMaster\Core\Config::get('URL') ?>. The system sends these emails because it detected that something has changed on your site. You received this email because you are a member of the site. You can remove yourself from the site by visiting: <?php echo $site->getURL();?>
</p>
