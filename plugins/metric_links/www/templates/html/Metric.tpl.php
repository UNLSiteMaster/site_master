<?php
    $suppressed_domains = array();
    $suppressed_domains_filename = dirname(dirname(dirname(__DIR__))) . '/data/suppressed_domains.txt';
    if (file_exists($suppressed_domains_filename)) {
        $suppressed_domains_file_contents = file_get_contents($suppressed_domains_filename);
        $exploded_domains = explode("\n", $suppressed_domains_file_contents);

        foreach ($exploded_domains as $single_domain) {
            $suppressed_domains[] = trim($single_domain);
        }
    }
?>
This metric scans all links and reports links that no longer work or are redirecting.
Some domains may be omitted from this metric due to erroneous and inconsistent HTTP status codes.

<?php if (count($suppressed_domains) === 0): ?>
    <p style="font-weight: bold; margin-top: 0.32em;">There are currently no omitted domains.</p>
<?php else: ?>
    <details style="margin-top: 0.32em;">
        <summary>Omitted Domains</summary>
        <ul>
            <?php foreach ($suppressed_domains as $domain): ?>
                <li><code style="background-color: var(--bg-code); border: none;"><?php echo $domain; ?></code></li>
            <?php endforeach; ?>
        </ul>
    </details>
<?php endif; ?>
