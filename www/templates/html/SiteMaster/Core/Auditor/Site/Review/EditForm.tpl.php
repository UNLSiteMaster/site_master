<?php
use \SiteMaster\Core\Auditor\Site\Review;

/**
 * @var $context \SiteMaster\Core\Auditor\Site\Review\EditForm
 */
?>

<form method="POST">
    <ul>
        <li>
            <?php 
            $date_scheduled = '';
            if ($context->review) {
                $date_scheduled = date('Y-m-d', strtotime($context->review->date_scheduled));
            }
            ?>
            <label for="date_scheduled">The review will be started on:</label>
            <input id="date_scheduled" name="date_scheduled" type="date" value="<?php echo $date_scheduled ?>" />
        </li>
        <li>
            <label id="status">Current Status of the review</label>
            <select id="status">
                <option 
                    value="<?php echo Review::STATUS_SCHEDULED ?>"
                    <?php echo ($context->review && $context->review->status == Review::STATUS_SCHEDULED)?'selected="selected"':'' ?>
                    >
                    Scheduled
                </option>
                <option
                    value="<?php echo Review::STATUS_IN_REVIEW ?>"
                    <?php echo ($context->review && $context->review->status == Review::STATUS_IN_REVIEW)?'selected="selected"':'' ?>
                    >
                    In Review
                </option>
                <option
                    value="<?php echo Review::STATUS_REVIEW_FINISHED ?>"
                    <?php echo ($context->review && $context->review->status == Review::STATUS_REVIEW_FINISHED)?'selected="selected"':'' ?>
                    >
                    Finished
                </option>
            </select>
        </li>
        <li>
            <label for="internal_notes">Internal Notes</label>
            <textarea name="internal_notes" id="internal_notes"><?php echo ($context->review)?$context->review->internal_notes:'' ?></textarea>
        </li>
        <li>
            <label for="public_notes">Public Notes</label>
            <textarea name="public_notes" id="public_notes"><?php echo ($context->review)?$context->review->public_notes:'' ?></textarea>
        </li>
    </ul>
    <input type="hidden" name="action" value="edit" />
    <button type="submit">Save</button>
</form>