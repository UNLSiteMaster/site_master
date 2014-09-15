<p>
    There are a total of <?php echo $context->sites->count(); ?> in the system.
</p>

<table class="sortable">
    <thead>
    <tr>
        <td>
            Site
        </td>
        <td>
            Pages
        </td>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($context->sites as $site) {
        ?>
        <tr>
            <td>
                <a href="<?php echo $site->base_url ?>"><?php echo $site->base_url;?></a>
                <?php if (!empty($site->title)) {?>
                    <br />
                    <?php echo $site->getTitle() ?>
                <?php }?>
                <div>
                    <a href="<?php echo $site->getURL() ?>">view</a> | <a href="<?php echo $site->getURL() ?>edit/">edit</a>
                </div>
            </td>
            <td>
                <?php echo $site->getPageCount() ?>
            </td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>