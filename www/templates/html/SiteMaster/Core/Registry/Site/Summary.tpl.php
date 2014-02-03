<a href="<?php echo $context->base_url ?>"><?php echo $context->getTitle(); ?></a>
<?php
echo $savvy->render($context->getApprovedMembers(), 'SiteMaster/Core/Registry/Site/Members/Summary.tpl.php');
?>