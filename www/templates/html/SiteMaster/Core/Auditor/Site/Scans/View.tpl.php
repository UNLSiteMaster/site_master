<?php
/**
 * @var $context \SiteMaster\Core\Auditor\Site\Scans\View
 */
?>

<table class="dcf-table" data-sortlist="[[0,0],[2,0]]">
    <thead>
        <tr>
            <th>Date Scanned</th>
            <th>Status</th>
            <th>Score</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($context->scans as $scan): ?>
        <tr>
            <td>
                <a href="<?php echo $scan->getURL() ?>"><?php echo $scan->end_time ?></a>
            </td>
            <td>
                <?php echo $scan->status ?> 
            </td>
            <td>
                <?php if ($scan->isPassFail()): ?>
                    <?php
                    echo $scan->gpa . '% of pages are passing';
                    
                    if ($scan->gpa == 100) {
                        echo ' (Looks Good)';
                    } else {
                        echo ' (Needs Work)';
                    }
                    ?>
                <?php else: ?>
                    <?php echo $scan->gpa . ' GPA'; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>