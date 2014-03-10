<?php
$scan = $context->getScan();
$site = $scan->getSite();
?>

<div class="pages info-section">
    <header>
        <h3>Pages</h3>
        <div class="subhead">
            This is a list of all pages that we found on your site.
        </div>
    </header>
    <table data-sortlist="[[0,0],[2,0]]">
        <thead>
        <tr>
            <th>Path</th>
            <th>Grade</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($context as $page) {
            ?>
            <tr>
                <td>
                    <a href="<?php echo $page->getURL()?>"><?php echo $theme_helper->trimBaseURL($site->base_url, $page->uri) ?></a>
                </td>
                <td><?php echo $page->letter_grade ?></td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
</div>
