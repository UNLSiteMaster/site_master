<?php

$authPlugins = \Sitemaster\Core\Plugin\PluginManager::getManager()->getAuthPlugins();
$providers = array();
foreach ($authPlugins as $plugin) {
    $providers[] = $plugin->getProviderMachineName();
}
?>

    <div class="panel">
        <form class="dcf-form" action="<?php echo $context->getURL();?>" method="GET">
            <label for="query">Enter Query</label>
            <input type="text" id="query" name="query" placeholder="http://wwww.domain.com/" />
            <div class="row">
                <div class="small-6 columns">
                    Examples:
                    <ul>
                        <li>Site: absolute URI, must include protocol (http://wwww.domain.com/)</li>
                        <li>Person: provider:uid (username@google)
                            <p>Available providers are: <?php echo implode(', ', $providers); ?></p>
                        </li>
                    </ul>
                </div>
                <div class="small-6 columns">
                    <button type="submit" class="dcf-btn dcf-btn-primary dcf-float-right">Query</button>
                </div>
            </div>
        </form>
    </div>

<?php
if ($context->result) {
    echo $savvy->render($context->result);
}
?>