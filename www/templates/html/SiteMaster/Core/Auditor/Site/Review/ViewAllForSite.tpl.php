<?php
/**
 * @var $context \SiteMaster\Core\Auditor\Site\Review\ViewAllForSite
 */

$reviews = $context->getReviews();
?>

<a href="<?php echo $context->getURL() ?>edit/" class="button wdn-button">Schedule or Start a new review</a>

<?php echo $savvy->render($reviews, 'SiteMaster/Core/Auditor/Site/Reviews/Table.tpl.php') ?>
