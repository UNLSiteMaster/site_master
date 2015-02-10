<?php
/**
 * @var $context \SiteMaster\Core\Auditor\Site\Review\ViewAllForSite
 */

$reviews = $context->getReviews();
?>

<a href="<?php echo $context->getURL() ?>edit/" class="button wdn-button">Schedule or Start a new review</a>

<div class="reviews">
    <?php if ($reviews->count() == 0): ?>
        Sorry, no reviews were found.
    <?php else: ?>
        <table data-sortlist="[[0,0],[2,0]]">
            <thead>
                <tr>
                    <th>Date Scheduled</th>
                    <th>Status</th>
                    <th>Options</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reviews as $review): ?>
                    <tr>
                        <td><?php echo $review->date_scheduled; ?></td>
                        <td><?php echo $review->status; ?></td>
                        <td><a href="<?php echo $review->getEditURL() ?>">edit</a></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>
</div>