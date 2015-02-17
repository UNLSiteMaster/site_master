<?php
$authPlugins = \Sitemaster\Core\Plugin\PluginManager::getManager()->getAuthPlugins();
$providers = array();
foreach ($authPlugins as $plugin) {
    $providers[] = $plugin->getProviderMachineName();
}
?>
<form action="<?php echo $context->getURL();?>" method="GET">
    <label for="query"><span class="required">(required)</span> Enter Query</label>
    <input type="text" id="query" name="query" placeholder="http://www.domain.com/" value="<?php echo $context->query ?>" required />
    <div class="panel">
        Examples:
        <ul>
            <li>Site: absolute URI, must include protocol (http://www.domain.com/)</li>
            <li>Person: uid@provider (1111@UNL)
                <p>Available providers are: <?php echo implode(', ', $providers); ?></p>
            </li>
        </ul>
    </div>
    <button type="submit">Query</button>
</form>

<?php
if ($context->result) {
    echo $savvy->render($context->result);
}
?>