
<p>The <?php echo $context->getGroupName() ?> contains <?php echo $context->getSites()->count() ?> sites.</p>

<?php echo $savvy->render($context, 'SiteMaster/Core/Group/history-graph.tpl.php'); ?>

<?php $nav = $context->getGroupNavigation(); ?>

<?php if (!empty($nav)): ?>
    <h2>Group Navigation</h2>
    <ul>
        <?php foreach ($nav as $url=>$title): ?>
            <li><a href="<?php echo $url ?>"><?php echo $title ?></a></li>
        <?php endforeach; ?>
    </ul>
<?php endif;
