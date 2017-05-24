<?php $overrides = $context->getOverrides() ?>

<?php if (count($overrides) === 0): ?>
    <p>There are no overrides for this site.</p>
<?php else: ?>
    <?php foreach ($overrides as $override): ?>
        <?php $mark = $override->getMark(); ?>
        <?php $metric = $mark->getMetric(); ?>

        <h2><?php echo $mark->name ?></h2>
        <dl>
            <dt>Metric</dt>
            <dd><?php echo $metric->getName() ?></dd>

            <dt>Value</dt>
            <dd><?php echo (empty($override->value_found)?'(empty)':$override->value_found) ?></dd>

            <dt>Scope</dt>
            <dd><?php echo $override->scope ?></dd>
            
            <?php if ($override->scope !== 'SITE'): ?>
                <dt>Page</dt>
                <dd><?php echo (empty($override->url)?'site':'Only on page: ' . $override->url) ?></dd>
            <?php endif; ?>

            <dt>Date created</dt>
            <dd><?php echo $override->date_created ?></dd>

            <dt>Expires</dt>
            <dd><?php echo (empty($override->expires)?'never':$override->expires) ?></dd>
            
            <?php if ($override->scope === 'ELEMENT'): ?>
                <?php if (!empty($override->context)): ?>
                    <dt>HTML Context</dt>
                    <dd>
                        <pre><code><?php echo trim(htmlentities($override->getRaw('context'), ENT_COMPAT | ENT_HTML401, 'UTF-8', false))?></code></pre>
                    </dd>
                <?php endif; ?>

                <?php if (!empty($override->line)): ?>
                    <dt>HTML line number</dt>
                    <dd>
                        <?php echo $override->line ?>
                    </dd>
                <?php endif; ?>

                <?php if (!empty($override->col)): ?>
                    <dt>HTML column number</dt>
                    <dd>
                        <?php echo $override->col ?>
                    </dd>
                <?php endif; ?>
            <?php endif; ?>

            <dt>Reason for override</dt>
            <dd><?php echo $override->reason ?></dd>
            
            <dt>Options</dt>
            <dd>
                <form method="post">
                    <input type="hidden" name="delete_id" value="<?php echo $override->id ?>" />
                    <button>Delete this override</button>
                </form>
            </dd>
        </dl>
    <?php endforeach; ?>
<?php endif; ?>


