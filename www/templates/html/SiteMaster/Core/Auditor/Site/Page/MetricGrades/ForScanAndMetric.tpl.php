<?php
$scan           = $context->getScan();
$site           = $scan->getSite();
$site_pass_fail = $scan->isPassFail();

if ($context->count()) {
    ?>
    <table class="dcf-table">
        <thead>
        <tr>
            <th>Page</th>
            <?php
            if ($site_pass_fail) {
                ?>
                <th>Errors</th>
                <th>Notices</th>
                <?php
            } else {
                ?>
                <th>Grade</th>
                <th>Errors</th>
                <th>Notices</th>
                <?php
            }
            ?>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($context as $grade) {
            /**
             * @var $grade \SiteMaster\Core\Auditor\Site\Page\MetricGrade
             */
            $page = $grade->getPage();
            ?>
            <tr>
                <td>
                    <a href="<?php echo $page->getURL()?>#metric_<?php echo $grade->metrics_id ?>"><?php echo $theme_helper->trimBaseURL($site->base_url, $page->uri) ?></a>
                </td>
                <?php
                if (!$site_pass_fail) {
                    ?>
                    <td>
                        <?php echo $theme_helper->formatGrade($grade->point_grade, $grade->letter_grade, $site_pass_fail); ?>
                    </td>
                    <?php
                }
                ?>
                <td>
                    <?php
                    echo (int)$grade->num_errors;
                    ?>
                </td>
                <td>
                    <?php
                    echo (int)$grade->num_notices;
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



