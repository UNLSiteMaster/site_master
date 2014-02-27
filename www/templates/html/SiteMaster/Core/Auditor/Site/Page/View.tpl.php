<?php
$scan = $context->page->getScan();
?>
<header class="page-scan-header">
    <div class="letter-grade-container">
        <span class="letter-grade unknown"><?php echo $context->page->letter_grade?></span>
    </div>
    <div class="details">
        <span class="title">Page: <?php echo $context->page->getTitle(); ?></span>
        <span class="page-url"><?php echo $context->page->uri ?></span>
        <div class="scan-info">
            <span class="scanned-date">Scanned on: <?php echo $context->page->start_time ?></span>
            <a href="<?php echo $scan->getURL() ?>">Go back to the scan</a>
        </div>
        
    </div>
</header>
<div class="page-scan-content">
    <?php
    echo $savvy->render($context->page->getMetricGrades());
    ?>
</div>
