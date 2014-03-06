<?php
/**
 * @var $context \SiteMaster\Core\Events\Navigation\SiteCompile
 */
$nav = $context->getNavigation();
$site = $context->getSite();

?>
<div class="site-nav">
    <div class="head-container">
        <div class="left">
            <div class="title"><?php echo $site->getTitle();?></div>
            <div class="url">
                <?php echo $site->base_url;?>
                <a href="<?php echo $site->base_url;?>" class="external" title="open the external page"><img src="<?php echo \SiteMaster\Core\Config::get('URL') ?>www/images/external.png" alt="link to external site"/></a>
            </div>
        </div>
        <div class="right">
            <?php echo $savvy->render($site->getScanForm()); ?>
        </div>
    </div>
        
    <div class="menu-button site-nav">Menu</div>
    <nav class="clear-fix">
        <ul data-breakpoint="800" class="flexnav">
            <?php
            foreach ($nav as $url=>$title) {
                ?>
                <li>
                    <a href="<?php echo $url;?>"><?php echo $title; ?></a>
                </li>
            <?php
            }
            ?>
        </ul>
    </nav>
</div>

