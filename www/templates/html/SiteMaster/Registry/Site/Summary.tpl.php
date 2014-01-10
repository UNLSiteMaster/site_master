<div class="large-12 columns panel">
    <a href="<?php echo $context->base_url ?>"><?php echo $context->getTitle(); ?></a>
    <?php
    echo $savvy->render($context->getMembers(), 'SiteMaster/Registry/Site/Members/Summary.tpl.php');
    ?>
</div>