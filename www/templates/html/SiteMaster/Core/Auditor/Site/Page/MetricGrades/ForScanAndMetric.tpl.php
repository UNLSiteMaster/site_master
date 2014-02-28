<?php
$scan = $context->getScan();
$site = $scan->getSite();
?>

<table>
    <thead>
    <tr>
        <th>Page</th>
        <th>Grade</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($context as $grade) {
        $page = $grade->getPage();
        ?>
        <tr>
            <td>
                <a href="<?php echo $page->getURL()?>"><?php echo $theme_helper->trimBaseURL($site->base_url, $page->uri) ?></a>
            </td>
            <td><?php echo $grade->letter_grade ?></td>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>
