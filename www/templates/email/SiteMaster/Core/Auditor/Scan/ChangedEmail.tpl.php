<?php
/**
 * @var $context \SiteMaster\Core\Auditor\Scan\CompletedEmail
 * @var $site \SiteMaster\Core\Registry\Site
 */
$site = $context->scan->getSite();

$previous_scan = $context->scan->getPreviousScan();
?>
<p>
    Hello!
</p>

<p>
    <?php echo $site->getTitle() ?> has changed! View the new report at <?php echo $site->getURL();?>.
</p>
<p>
    The current GPA is <?php echo $context->scan->gpa ?>.
    
    <?php
    if ($previous_scan) {
        $diff = abs($previous_scan->gpa - $context->scan->gpa);
        
        if ($diff == 0) {
            echo 'That is the same GPA as last time.';
        } else if ($context->scan->gpa < $previous_scan->gpa) {
            echo 'That is a decrease of ' . $diff;
        } else {
            echo 'That is an increase of ' . $diff . '.  Good job!';
        }
    }
    ?>
</p>

<p>
    This is an automated email sent by <?php echo \SiteMaster\Core\Config::get('SITE_TITLE') ?>.  You will receive one of these emails whenever we notice that something changed on your site.  This tool is here to help you ensure the best experience for your users by showing you potential problems.
</p>

<p>
    Thank you,<br />
    <?php echo \SiteMaster\Core\Config::get('EMAIL_SIGNATURE') ?>
</p>

<p>
    You received this email because you are a member of the site.  You can remove yourself from the site by visiting: <?php echo $site->getURL();?>
</p>
