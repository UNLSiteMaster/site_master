<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <ol>
        <li>
            <label for="site_title">Site Title</label>
            <input type="text" id="site_title" name="title" value="<?php echo $context->site->title ?>" autofocus />
        </li>
        <li>
            <label for="support_email">Support Email Address</label>
            <input type="email" id="support_email" name="support_email" multiple value="<?php echo $context->site->support_email ?>" />
        </li>
        <li>
            <label for="support_groups">Support assignments for this site (separated by spaces and quoted if the group name includes spaces).</label>
            <input type="text" id="support_groups" name="support_groups" multiple value="<?php echo $context->site->support_groups ?>" />
        </li>
        <li>
            <label for="site_map_url">Absolute URL to the site map for your site</label>
            <input type="url" id="site_map_url" name="site_map_url" value="<?php echo $context->site->site_map_url ?>" />
        </li>
        <li>
            <label for="site_map_url">Absolute URL to the site map for your site</label>
            <input type="url" id="site_map_url" name="site_map_url" value="<?php echo $context->site->site_map_url ?>" />
            <div class="help-text">
                <p>
                    See <a href="http://www.sitemaps.org/protocol.html">sitemaps.org</a> for details about the site map protocol.  There are three scanning methos for your site:</p>
                <ul>
                    <li><?php echo \SiteMaster\Core\Registry\Site::CRAWL_METHOD_CRAWL_ONLY ?> - only crawl the site to discover pages</li>
                    <li><?php echo \SiteMaster\Core\Registry\Site::CRAWL_METHOD_SITE_MAP_ONLY ?> - only use the site map to discover pages</li>
                    <li><?php echo \SiteMaster\Core\Registry\Site::CRAWL_METHOD_HYBRID ?> - (default) Use both methods, crawling and a sitemap to discover pages</li>
                </ul>
                <p>
                    This site is currently using the <strong><?php echo $context->site->crawl_method ?></strong> method to discover pages.  If you would like to use a different method, please contact the <?php echo \SiteMaster\Core\Config::get('SITE_TITLE') ?> administrator.
                </p>
            </div>
        </li>
        <li>
            <fieldset>
                <?php 
                //Store in temp variables to make things easier to read (shorter)
                $production  = \SiteMaster\Core\Registry\Site::PRODUCTION_STATUS_PRODUCTION;
                $development = \SiteMaster\Core\Registry\Site::PRODUCTION_STATUS_DEVELOPMENT;
                $archived    = \SiteMaster\Core\Registry\Site::PRODUCTION_STATUS_ARCHIVED;
                ?>
                <legend>Production Status (the production status that the site is currently in)</legend>
                <ul>
                    <li>
                        <input
                            id="production_status_production"
                            type="radio"
                            <?php echo ($context->site->production_status == $production?'checked="checked"':'')?>
                            name="production_status"
                            value="<?php echo $production ?>"
                            />
                        <label for="production_status_production">Production - this site is in production and will be scanned more often than development sites.</label>
                    </li>
                    <li>
                        <input
                            id="production_status_development"
                            type="radio"
                            <?php echo ($context->site->production_status == $development?'checked="checked"':'')?>
                            name="production_status"
                            value="<?php echo $development ?>"
                            />
                        <label for="production_status_development">Development - this site is in development.  It is expected that manual scans will be started by developers, so we will not auto-scan this site as often as production sites.</label>
                    </li>
                    <li>
                        <input
                            id="production_status_archived"
                            type="radio"
                            <?php echo ($context->site->production_status == $archived?'checked="checked"':'')?>
                            name="production_status"
                            value="<?php echo $archived ?>"
                            />
                        <label for="production_status_archived">Archived - this is an old site that has since been replaced by a newer one.  It is not being advertised, linked to, and is clearly an archived version of an old site.  Archived sites will not be scanned.</label>
                    </li>
                </ul>
            </fieldset>
        </li>
    </ol>

    <input type="hidden" name="action" value="edit" />
    <button type="submit">Save</button>
</form>

<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <input type="hidden" name="action" value="delete" />
    <button type="submit" id="delete-site">Delete this site</button>
</form>