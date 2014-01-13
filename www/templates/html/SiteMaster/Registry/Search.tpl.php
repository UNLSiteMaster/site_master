<?php
$authPlugins = \Sitemaster\Plugin\PluginManager::getManager()->getAuthPlugins();
$providers = array();
foreach ($authPlugins as $plugin) {
    $providers[] = $plugin->getProviderMachineName();
}
?>
<form action="<?php echo $context->getURL();?>" method="GET">
    <label for="query">Enter Query</label>
    <input type="text" id="query" name="query" placeholder="http://wwww.domain.com/" />
    Examples:
    <ul>
        <li>Site: absolute URI, must include protocol (http://wwww.domain.com/)</li>
        <li>Person: provider:uid (google:1111) 
            <p>Available providers are: <?php echo implode(', ', $providers); ?></p>
        </li>
    </ul>

    <button type="submit">Query</button>
    </div>
</form>

<?php
if ($context->result) {
    echo $savvy->renderWithTheme($context->result);
}
?>