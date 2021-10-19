<div class="dcf-mt-6 results">
    <?php
        if ($context->getInnerIterator()->count() == 0) {
            ?>
            Sorry, no sites could be found
        <?php
    } else {
        ?>
        <h2>Results</h2>
        <?php
        foreach ($context as $site) {
            echo $savvy->render($site, 'SiteMaster/Core/Registry/Site/Summary.tpl.php');
        }
    }
    ?>
</div>

