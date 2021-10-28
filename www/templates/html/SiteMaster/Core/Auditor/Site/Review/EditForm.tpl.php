<?php
use \SiteMaster\Core\Auditor\Site\Review;

/**
 * @var $context \SiteMaster\Core\Auditor\Site\Review\EditForm
 */
?>

<form class="dcf-form" method="POST">
    <ul class="dcf-list-bare">
        <li>
            <?php
            $date_scheduled = '';
            if ($context->review) {
                $date_scheduled = $context->review->getDateScheduled()->format('Y-m-d');
            }
            ?>
            <label for="date_scheduled"><small class="dcf-required">Required</small> The review will be started on:</label>
            <input id="date_scheduled" name="date_scheduled" type="date" value="<?php echo $date_scheduled ?>" />
        </li>
        <li>
            <label for="status">Current Status of the review</label>
            <select id="status" name="status">
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
        <li>
        <li>
            <label for="result">Result of the review</label>
            <select id="result" name="result">
                <option value="">
                    
                </option>
                <option
                    value="<?php echo Review::RESULT_NEEDS_WORK ?>"
                    <?php echo ($context->review && $context->review->result == Review::RESULT_NEEDS_WORK)?'selected="selected"':'' ?>
                    >
                    The site needs work
                </option>
                <option
                    value="<?php echo Review::RESULT_OKAY ?>"
                    <?php echo ($context->review && $context->review->result == Review::RESULT_NEEDS_WORK)?'selected="selected"':'' ?>
                    >
                    The site is okay
                </option>
            </select>
        </li>
        </li>
    </ul>
    <input type="hidden" name="action" value="edit" />
    <?php $csrf_helper->insertToken() ?>
    <button type="submit"  class="dcf-btn dcf-btn-primary">Save</button>
</form>
