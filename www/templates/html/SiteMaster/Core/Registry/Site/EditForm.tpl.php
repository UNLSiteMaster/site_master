<form action="<?php echo $context->getEditURL(); ?>" method="POST">
    <ol>
        <li>
            <label for="site_title">Site Title</label>
            <input type="text" id="site_title" name="title" value="<?php echo $context->site->title ?>" autofocus />
        </li>
        <li>
            <label for="support_email">Support Email Address</label>
            <input type="email" id="support_email" name="support_email" value="<?php echo $context->site->support_email ?>" />
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