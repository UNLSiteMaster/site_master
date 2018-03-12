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

<h2>Honor Roll</h2>

<p>These sites are doing exceptionally well. Making quality websites is time intensive and requires a lot of still. It is hard work, but your users appreciate it. Congratulations to all of the sites on these lists.</p>

<h3>Sites with a score of 100%</h3>

<?php $sites = $context->getHonorRoll100() ?>

<p>These sites have 100% of their pages passing all graded metrics. There are <?php $sites->count() ?> sites on this list.</p>

<ul>
    <?php foreach ($sites as $site): ?>
        <?php $scan = $site->getLatestScan(true); ?>
        <li>
            <a href="<?php echo $site->getURL(); ?>"><?php echo $site->base_url ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<h3>Sites with a score of 90%</h3>
<?php $sites = $context->getHonorRoll90() ?>

<p>These sites have at least 90% of their pages passing all graded metrics. There are <?php $sites->count() ?> sites on this list.</p>

<table>
    <thead>
        <tr>
            <th>Site</th>
            <th>Score</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($sites as $site): ?>
            <?php $scan = $site->getLatestScan(true); ?>
            <tr>
                <td><a href="<?php echo $site->getURL(); ?>"><?php echo $site->base_url ?></a></td>
                <td><?php echo $scan->gpa ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
