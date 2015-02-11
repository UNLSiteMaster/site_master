<?php
use \SiteMaster\Core\Auditor\Site\Review;
/**
 * @var $context \SiteMaster\Core\Auditor\Site\Review\View
 */
?>

<?php if (!empty($context->review->result)): ?>
    <?php
    $result = '';
    switch ($context->review->result) {
        case Review::RESULT_NEEDS_WORK:
            $result = 'Site needs work';
            break;
        case Review::RESULT_OKAY:
            $result = 'Site is okay';
            break;
    }
    ?>
    <p>
        Result: <?php echo $result?>
    </p>
<?php endif; ?>

<p>
    <?php echo $context->review->public_notes ?>
</p>