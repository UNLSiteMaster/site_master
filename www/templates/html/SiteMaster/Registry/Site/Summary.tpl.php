<a href="<?php echo $context->base_url ?>"><?php echo $context->getTitle(); ?></a>
<?php
echo $savvy->renderWithTheme($context->getMembers(), 'SiteMaster/Registry/Site/Members/Summary.tpl.php');
?>