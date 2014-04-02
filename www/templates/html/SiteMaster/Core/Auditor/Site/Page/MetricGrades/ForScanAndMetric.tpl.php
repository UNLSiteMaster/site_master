<?php
$scan           = $context->getScan();
$site           = $scan->getSite();
$site_pass_fail = $scan->isPassFail();

if ($context->count()) {
    ?>
    <table>
        <thead>
        <tr>
            <th>Page</th>
            <?php
            if ($site_pass_fail) {
                ?>
                <th>Marks</th>
                <?php
            } else {
                ?>
                <th>Grade</th>
                <?php
            }
            ?>
            
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($context as $grade) {
            $page = $grade->getPage();
            ?>
            <tr>
                <td>
                    <a href="<?php echo $page->getURL()?>#metric_<?php echo $grade->metrics_id ?>"><?php echo $theme_helper->trimBaseURL($site->base_url, $page->uri) ?></a>
                </td>
                <td>
                    <?php
                    if ($site_pass_fail) {
                        $marks = $grade->getMarks();
                        echo $marks->count();
                    } else {
                        echo $theme_helper->formatGrade($grade->point_grade, $grade->letter_grade, $site_pass_fail);
                    }
                    ?>
                </td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <?php
} else {
    ?>
    <p>Everything looks good!</p>
    <?php
}



