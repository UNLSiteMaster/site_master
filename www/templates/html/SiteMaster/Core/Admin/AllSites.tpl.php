<p>
    There are a total of <?php echo $context->sites->count(); ?> in the system.
</p>

<table class="sortable">
    <thead>
    <tr>
        <th>
            Site
        </th>
        <th>
            Pages
        </th>
        <th>
            Error
        </th>
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
                    <?php 
                    if (strlen($site->getTitle()) > 40) {
                        echo substr($site->getTitle(), 0, 40) . '...';
                    } else {
                        echo $site->getTitle();
                    }
                    ?>
                <?php }?>
                <div>
                    <a href="<?php echo $site->getURL() ?>">view</a> | <a href="<?php echo $site->getURL() ?>edit/">edit</a>
                </div>
            </td>
            <td>
                <?php echo $site->getPageCount() ?>
            </td>
            <td>
                <?php
                if ($site->hasConnectionError()) {
                    echo $site->timeSinceLastSuccess()->format('%d days') . ' (' . (int)$site->http_code . '|' . (int)$site->curl_code . ')';
                }
                ?>
            </td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>