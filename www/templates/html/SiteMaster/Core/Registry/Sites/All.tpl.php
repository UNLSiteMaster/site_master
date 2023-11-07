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
                            <div class="ownership dcf-txt-sm">
                                <?php 
                                    $owner = 'None';
                                    $primary = 'None';
                                    $secondary = 'None';

                                    $owner_members = $site->getMembersWithRoleName('Owner');
                                    if (count($owner_members) > 0) {
                                        $owner_members->rewind();
                                        $owner_user = $owner_members->current()->getUser();
                                        $owner = $owner_user->first_name . " " . $owner_user->last_name;
                                    }

                                    $primary_members = $site->getMembersWithRoleName('Primary Site Manager');
                                    if (count($primary_members) > 0) {
                                        $primary_members->rewind();
                                        $primary_user = $primary_members->current()->getUser();
                                        $primary = $primary_user->first_name . " " . $primary_user->last_name;
                                    }

                                    $secondary_members = $site->getMembersWithRoleName('Secondary Site Manager');
                                    if (count($secondary_members) > 0) {
                                        $secondary_members->rewind();
                                        $secondary_user = $secondary_members->current()->getUser();
                                        $secondary = $secondary_user->first_name . " " . $secondary_user->last_name;
                                    }
                                ?>
                                <div>
                                    <span class="dcf-bold">Owner:</span> <?php echo $owner; ?>,
                                    <span class="dcf-bold">Primary Site Manager:</span> <?php echo $primary; ?>,
                                    <span class="dcf-bold">Secondary Site Manager:</span> <?php echo $secondary; ?>

                                    <?php if ($site->isCurrentUserAdmin()): ?>
                                        (<a href="<?php echo $site->getURL() ?>members/">Edit roles</a>)
                                    <?php else: ?>
                                        (<a href="<?php echo $site->getURL() ?>join/">Edit my roles</a>)
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="group">In the group: <a href="<?php echo $base_url . 'groups/'.$site->getPrimaryGroupName().'/' ?>"><?php echo $site->getPrimaryGroupName() ?></a></div>
                        </div>
                        <?php $page_count = $site->getPageCount(); ?>
                        <?php if ($page_count !== false): ?>
                            <div class="page_count">
                                <div class="value">
                                    <?php echo $page_count; ?>
                                </div>
                                <div class="metric">
                                    <?php if ($page_count == 1): ?>
                                        Page
                                    <?php else: ?>
                                        Pages
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
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
