<!doctype html>
<html class="no-js" lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Foundation | Welcome</title>
    <link rel="stylesheet" href="<?php echo \SiteMaster\Core\Config::get('URL') ?>plugins/theme_foundation/www/themes/foundation/html/css/foundation.css" />
    <script src="<?php echo \SiteMaster\Core\Config::get('URL') ?>plugins/theme_foundation/www/themes/foundation/html/js/modernizr.js"></script>
    
    <?php
    $style_sheets_event = \SiteMaster\Core\Plugin\PluginManager::getManager()->dispatchEvent(
        \SiteMaster\Core\Events\Theme\RegisterStyleSheets::EVENT_NAME,
        new \SiteMaster\Core\Events\Theme\RegisterStyleSheets()
    );

    foreach ($style_sheets_event->getStyleSheets() as $url=>$media) {
        ?>
        <link rel="stylesheet" href="<?php echo $url?>" media="<?php echo $media ?>"/>
        <?php
    }
    
    $scripts_event = \SiteMaster\Core\Plugin\PluginManager::getManager()->dispatchEvent(
        \SiteMaster\Core\Events\Theme\RegisterScripts::EVENT_NAME,
        new \SiteMaster\Core\Events\Theme\RegisterScripts()
    );

    foreach ($scripts_event->getScripts() as $url=>$type) {
        ?>
        <script src="<?php echo $url?>" type="<?php echo $type ?>"></script>
        <?php
    }
    ?>
</head>
<body>
<nav class="top-bar" data-topbar>
    <ul class="title-area">
        <li class="name">
            <h1><a href="<?php echo \SiteMaster\Core\Config::get('URL')?>">Site Master</a></h1>
        </li>
    </ul>
    <section class="top-bar-section">
        <!-- Left Nav Section -->
        <ul class="left has-dropdown">
            <?php
            $mainNav = \SiteMaster\Core\Plugin\PluginManager::getManager()->dispatchEvent(
                \SiteMaster\Core\Events\Navigation\MainCompile::EVENT_NAME,
                new \SiteMaster\Core\Events\Navigation\MainCompile()
            );

            echo $savvy->render($mainNav);
            ?>
        </ul>
        <!-- Right Nav Section -->
        <ul class="right">
            <li class="has-dropdown">
                <?php
                if ($user = \SiteMaster\Core\User\Session::getCurrentUser()) {
                    $logoutURL = \SiteMaster\Core\Config::get('URL') . 'logout/';
                    if ($authPlugin = $user->getAuthenticationPlugin()) {
                        $logoutURL = $authPlugin->getLogoutURL();
                    }
                    ?>
                    <a href="#"><?php echo $user->first_name ?></a>
                    <ul class="dropdown">
                        <li>
                            <a href="<?php echo \SiteMaster\Core\Config::get('URL') ?>user/settings/">Settings</a>
                            <a href="<?php echo $logoutURL ?>">Log Out</a>
                        </li>
                    </ul>
                     <?php
                } else {
                    ?>
                    <a href="#">Login</a>
                    <ul class="dropdown">
                        <li>
                            <?php
                            $authPlugins = \Sitemaster\Core\Plugin\PluginManager::getManager()->getAuthPlugins();
                            
                            foreach ($authPlugins as $plugin) {
                                ?>
                                <a href="<?php echo $plugin->getLoginURL(); ?>"><?php echo $plugin->getProviderHumanName() ?></a>
                            <?php
                            }
                            ?>
                        </li>
                    </ul>
                    <?php
                }
                ?>
            </li>
        </ul>
    </section>
</nav>

<div class="row">
    <div class="large-12 columns">
        <?php
        foreach ($app->getFlashBagMessages() as $message) {
            echo $savvy->render($message);
        }
        ?>
    </div>
</div>

<div class="row">
    <div class="large-12 columns">
        <h1><?php echo $context->output->getPageTitle() ?></h1>
    </div>
</div>

<div class="row">
    <div class="large-12 columns">
        <?php
        if (isset($app->options['site_id'])) {
            $site = \SiteMaster\Core\Registry\Site::getByID($app->options['site_id']);
            if ($site) {
                $siteNav = \SiteMaster\Core\Plugin\PluginManager::getManager()->dispatchEvent(
                    \SiteMaster\Core\Events\Navigation\SiteCompile::EVENT_NAME,
                    new \SiteMaster\Core\Events\Navigation\SiteCompile($site)
                );
        
                echo $savvy->render($siteNav);
            }
        }
        ?>
    </div>
</div>

<div class="row">
    <div class="large-12 columns">
        <?php
        echo $savvy->render($context->output);
        ?>
    </div>
</div>


<script src="<?php echo \SiteMaster\Core\Config::get('URL') ?>plugins/theme_foundation/www/themes/foundation/html/js/jquery.js"></script>
<script src="<?php echo \SiteMaster\Core\Config::get('URL') ?>plugins/theme_foundation/www/themes/foundation/html/js/foundation.min.js"></script>
<script>
    $(document).foundation();
</script>
</body>
</html>