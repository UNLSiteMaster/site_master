<?php
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
                        <div class="general-details">
                            <div class="url">
                                <a href="<?php echo $site->getURL(); ?>"><?php echo $site->base_url ?></a>
                            </div>
                            <?php if ($site->base_url != $site->getTitle()): ?>
                            <div class="title">
                                <?php echo $site->getTitle() ?>
                            </div>
                            <?php endif ?>
                            <div class="group">In the group: <a href="<?php echo $base_url . 'groups/'.$site->getPrimaryGroupName().'/' ?>"><?php echo $site->getPrimaryGroupName() ?></a></div>
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
                                    if ($scan->isPassFail()) {
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
                </div>
            </li>
        <?php
        }
        ?>
    </ul>
<?php
}
