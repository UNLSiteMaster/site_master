<?php echo $savvy->render($context->mark); ?>

<dl class="fix-mark-details">
    <dt>Found on page</dt>
    <dd>
        <?php echo $context->page->uri ?> (<a href="<?php echo $context->page->getURL() ?>">view page report</a> or <a href="<?php echo $context->page->uri ?>" target="_blank">View Page</a>)
    </dd>
    <dt>Points Deducted from the Metric Grade</dt>
    <dd>
        <?php
            $points_deducted = $context->page_mark->points_deducted;
            if ($context->metric_grade->isPassFail()) {
                if ($context->page_mark->points_deducted) {
                    $points_deducted = $context->page_mark->points_deducted . ' Fail';
                } else {
                    $points_deducted = ' 0 Pass';
                }
            }
            if ($context->page_mark->points_deducted === '0.00') {
                $points_deducted = '0 (notice, this is informational and does not count toward the metric grade)';
            }
            echo $points_deducted;
        ?>
    </dd>
    
    <?php
    if (!empty($context->page_mark->value_found)) {
        ?>
        <dt>Value Found</dt>
        <dd><?php echo $context->page_mark->value_found ?></dd>
    <?php
    }
    ?>
    <dt>Location on the Page</dt>
    <?php
    $location = 'Page';
    if (!empty($context->page_mark->line) && !empty($context->page_mark->line)) {
        $location = 'Line ' . $context->page_mark->line . ', Column ' . $context->page_mark->col;
    }
    if (!empty($context->page_mark->context)) {
        $location .= '<br />HTML Context: <pre><code>' . trim(strip_tags($context->page_mark->getRaw('context'))) . '</code></pre>';
    }
    ?>
    <dd><?php echo $location ?></dd>
</dl>

<a href="<?php echo $context->page->getURL() ?>">Go back to the page report</a>

<div class="pull-right wdn-pull-right">
    <span class="machine_name">Machine Name: <?php echo $context->mark->machine_name ?></span>
</div>
