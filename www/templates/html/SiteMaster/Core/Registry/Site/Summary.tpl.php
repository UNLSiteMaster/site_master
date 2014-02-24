<div class="panel">
    <div class="site-header">
        <span class="site-title">
            <a href="<?php echo $context->getURL() ?>"><?php echo $context->getTitle() ?></a>
        </span>
        <span class="site-url">
            <?php echo $context->base_url ?>
            <a href="<?php echo $context->base_url;?>" class="external" title="open the external page"><img src="<?php echo \SiteMaster\Core\Config::get('URL') ?>www/images/external.png" alt="link to external site"/></a>
        </span>
    </div>
    <?php
    echo $savvy->render($context->getApprovedMembers(), 'SiteMaster/Core/Registry/Site/Members/Summary.tpl.php');
    ?>
</div>