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
            ?>
            <li class="site gpa-4 clear-fix">
                <a href="<?php echo $site->getURL(); ?>">
                    <div class="general-details">
                        <div class="url">
                            <?php echo $site->base_url ?>
                        </div>
                        <div class="title">
                            <?php echo $site->getTitle() ?>
                        </div>
                    </div>
                    <div class="gpa">
                        <div class="value">
                            4.0
                        </div>
                        <div class="metric">
                            GPA
                        </div>
                    </div>
                </a>
            </li>
        <?php
        }
        ?>
    </ul>
<?php
}
