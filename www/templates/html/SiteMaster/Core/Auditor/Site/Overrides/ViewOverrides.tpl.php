<?php $overrides = $context->getOverrides() ?>
<?php $canEdit = $context->canEdit() ?>

<p>Errors and notices can be overridden to help you focus on actual problems. When errors or notices are overridden, they will not be reported on future scans. By default, overrides will expire after one year, and metrics can customize that. To create an override, click 'fix' next to the notice and follow the instructions. Note that most metrics only allow overriding notices.</p>

<?php if (count($overrides) === 0): ?>
    <p>There are no overrides for this site.</p>
<?php else: ?>
    <?php foreach ($overrides as $override): ?>
        <?php $mark = $override->getMark(); ?>
        <?php $metric = $mark->getMetric(); ?>

        <h2><?php echo ucfirst(strtolower($override->scope)) ?> override for '<?php echo $mark->name ?>' notices from the '<?php echo $metric->getName() ?>' metric</h2>
        <dl>
            <dt>Matches this value</dt>
            <dd><?php echo (empty($override->value_found)?'(empty)':$override->value_found) ?></dd>
            
            <?php if ($override->scope !== 'SITE'): ?>
                <dt>Page</dt>
                <dd><?php echo (empty($override->url)?'site':'Only on page: ' . $override->url) ?></dd>
            <?php endif; ?>

            <dt>Expires</dt>
            <dd><?php echo (empty($override->expires)?'never':$override->expires) ?></dd>
            
            <?php if ($override->scope === 'ELEMENT'): ?>
                <?php if (!empty($override->context)): ?>
                    <dt>HTML Context</dt>
                    <dd>
                        <pre><code><?php echo trim(htmlentities($override->getRaw('context'), ENT_COMPAT | ENT_HTML401, 'UTF-8', false))?></code></pre>
                    </dd>
                <?php endif; ?>
            <?php endif; ?>

            <dt>Reason for override</dt>
            <dd><?php echo $override->reason ?></dd>
            
            <?php if ($canEdit): ?>
            <dt>Options</dt>
            <dd>
                <form method="post">
                    <input type="hidden" name="delete_id" value="<?php echo $override->id ?>" />
                    <?php $csrf_helper->insertToken() ?>
                    <button>Delete this override</button>
                </form>
            </dd>
            <?php endif; ?>
        </dl>
    <?php endforeach; ?>
<?php endif; ?>


