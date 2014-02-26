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
            <td><?php echo $theme_helper->trimBaseURL($site->base_url, $page->uri) ?></td>
            <td><?php echo $grade->letter_grade ?></td>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>