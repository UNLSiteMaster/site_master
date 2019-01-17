<?php
foreach ($context as $metric) {
    ?>
    <h2><?php echo $metric->getName() ?></h2>
    
    <div>
        <a href="<?php echo $context->getURL() . $metric->getMetricRecord()->id . '/' ?>" class="button dcf-btn">View Marks</a>
    </div>
    
    <?php
    try {
        $description = $savvy->render($metric);
    } catch (\Savvy_TemplateException $e) {
        $description = "No information available.";
    }
    ?>
    
    <div class="metric-description">
        <?php echo $description ?>
    </div>
    
    <?php
}
