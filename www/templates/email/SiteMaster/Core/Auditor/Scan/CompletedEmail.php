<?php
/**
 * @var $context \SiteMaster\Core\Auditor\Scan\CompletedEmail
 * @var $site \SiteMaster\Core\Registry\Site
 */
$site = $context->scan->getSite();
?>

<p>
    <?php echo $site->getTitle() ?> has changed! <a href="<?php $site->getURL();?>">View the new report</a>.
</p>
<p>
    You received this email because you are a member of the site.  You can remove yourself from the site by visiting: <?php $site->getURL();?>
</p>
