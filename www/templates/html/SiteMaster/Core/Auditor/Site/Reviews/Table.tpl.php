<div class="reviews">
    <?php if ($context->count() == 0): ?>
        Sorry, no reviews were found.
    <?php else: ?>
        <table class="dcf-table" data-sortlist="[[0,0],[2,0]]">
            <thead>
            <tr>
                <th>Date Scheduled</th>
                <th>Status</th>
                <th>Options</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($context as $review): ?>
                <tr>
                    <td><?php echo $review->date_scheduled; ?></td>
                    <td><?php echo $review->status; ?></td>
                    <td>
                        <?php if ($review->canEdit($user->getRawObject())): ?>
                        <a href="<?php echo $review->getEditURL() ?>">edit</a>
                        <?php endif; ?>
                        <?php if ($review->isComplete($user->getRawObject())): ?>
                            or <a href="<?php echo $review->getURL() ?>">view</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>
</div>