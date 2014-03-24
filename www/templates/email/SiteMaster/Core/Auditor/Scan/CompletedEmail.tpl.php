<?php
/**
 * @var $context \SiteMaster\Core\Auditor\Scan\CompletedEmail
 * @var $site \SiteMaster\Core\Registry\Site
 */
$site = $context->scan->getSite();

$previous_scan = $context->scan->getPreviousScan();
?>

<p>
    <?php echo $site->getTitle() ?> has changed! <a href="<?php $site->getURL();?>">View the new report</a>.
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
    Thank you,<br />
    The Web Developer Network
</p>

<p>
    You received this email because you are a member of the site.  You can remove yourself from the site by visiting: <?php echo $site->getURL();?>
</p>
