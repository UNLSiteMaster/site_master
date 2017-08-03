<?php
$metrics = $context->getMetrics();
$names = array();
foreach ($metrics as $metric) {
    $names[] = $metric->getName();
}

$names[count($names)-1] = 'and ' . $names[count($names)-1];

?>

<p>The <?php echo $context->getGroupName() ?> group uses the <?php echo implode(', ', $names) ?> metrics. The group contains <?php echo $context->getSites()->count() ?> sites. The group has a total of <?php echo $context->getTotalPages() ?> pages.</p>

<?php $nav = $context->getGroupNavigation(); ?>
<?php if (!empty($nav)): ?>
    <h2>Group Pages</h2>
    <ul>
        <?php foreach ($nav as $url=>$title): ?>
            <li><a href="<?php echo $url ?>"><?php echo $title ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php echo $savvy->render($context, 'SiteMaster/Core/Group/history-graph.tpl.php'); ?>
