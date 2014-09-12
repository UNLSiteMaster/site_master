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
                <a href="<?php echo $site->getURL() ?>"><?php echo $site->getTitle() ?></a>
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