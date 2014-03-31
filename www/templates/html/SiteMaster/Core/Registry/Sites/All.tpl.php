<?php
$site_pass_fail = \SiteMaster\Core\Config::get('SITE_PASS_FAIL');

if (!$context->count()) {
    ?>
    No sites
<?php
} else {
    ?>
    <ul class="site-list">
        <?php
        foreach ($context as $site) {
            $scan = $site->getLatestScan();
            $gpa = false;
            ?>
            <li class="site clear-fix">
                <div class="panel clear-fix">
                    <a href="<?php echo $site->getURL(); ?>">
                        <div class="general-details">
                            <div class="url">
                                <?php echo $site->base_url ?>
                            </div>
                            <div class="title">
                                <?php echo $site->getTitle() ?>
                            </div>
                        </div>
                        <?php
                        if ($scan) {
                            ?>
                            <div class="gpa">
                                <div class="value">
                                    <?php echo $scan->gpa; ?>
                                </div>
                                <div class="metric">
                                    <?php
                                    if ($site_pass_fail) {
                                        if ($scan->gpa == 100) {
                                            echo 'Looks Good';
                                        } else {
                                            echo 'Needs Work';
                                        }
                                    } else {
                                        echo "GPA";
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </a>
                </div>
            </li>
        <?php
        }
        ?>
    </ul>
<?php
}
