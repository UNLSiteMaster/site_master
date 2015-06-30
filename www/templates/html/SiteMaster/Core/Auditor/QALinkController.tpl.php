<p>
    URL To Test: <?php echo $context->url ?>
</p>

<?php if (!$context->site): ?>
    <p>
        We don't know about this site.  Please add it and we will scan it!
        <a href="<?php \SiteMaster\Core\Config::get('URL') ?>/sites/add/" class="button wdn-button">Register the site now</a>
    </p>
<?php elseif (!$context->scan): ?>
    <p>
        This site has not been scanned yet.  Would you like a run a scan now?
        <?php echo $savvy->render($context->site->getScanForm()); ?>
    </p>
<?php elseif (!$context->page): ?>
    <p>
        We didn't find this page in the last scan.  Would you like to scan it now?
        <?php echo $savvy->render($context->getPageScanForm()); ?>
    </p>
<?php else: ?>
    <p>
        There was an unknown problem...
    </p>
<?php endif; ?>
