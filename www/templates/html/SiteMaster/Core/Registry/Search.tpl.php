<?php
$authPlugins = \Sitemaster\Core\Plugin\PluginManager::getManager()->getAuthPlugins();
$providers = array();
foreach ($authPlugins as $plugin) {
    $providers[] = $plugin->getProviderMachineName();
}
?>
<form class="dcf-form" action="<?php echo $context->getURL();?>" method="GET">
    <div class="dcf-form-group">
      <label for="query">Enter Query <small class="required dcf-required">Required</small></label>
      <input type="text" id="query" name="query" value="<?php echo $context->query ?>" required />
    </div>
    <div class="panel">
        Examples:
        <ul class="dcf-mb-0">
            <li>Site: absolute URI, must include protocol (https://www.domain.com/)</li>
            <li class="dcf-mb-0">Person: uid@provider (1111@UNL)
                <p>Available providers are: <?php echo implode(', ', $providers); ?></p>
            </li>
        </ul>
    </div>
    <button class="dcf-btn dcf-btn-primary" type="submit">Query</button>
</form>

<?php
if ($context->result) {
    echo $savvy->render($context->result);
}
?>
