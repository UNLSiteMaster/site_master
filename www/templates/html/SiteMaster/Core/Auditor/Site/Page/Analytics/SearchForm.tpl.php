<p>This form allows site developers to search for HTML element or class usage across the sites that we scan. This can help inform design, development, and education decisions.</p>

<form method="get">
    <fieldset>
        <legend>Search for usage</legend>
        <ul>
            <li>
                <label>
                    Data Type
                    <select name="data_type">
                        <option value="CLASS">class</option>
                        <option value="ELEMENT">element</option>
                    </select>
                </label>
            </li>
            <li>
                <label>
                    Key (class or element name)
                    <input name="data_key" type="text" value="<?php echo (isset($context->options['data_key']))?$context->options['data_key']:'' ?>" />
                </label>
            </li>
            <li>
                <button>Search</button>
            </li>
        </ul>
    </fieldset>
</form>

<?php if ($results = $context->getResults()): ?>
    <h2>Results</h2>
    <?php if ($results->count() == 0): ?>
        Sorry, I couldn't find anything for you.
    <?php else: ?>
        Total Pages that Match: <?php echo $context->getTotal() ?>
        <table>
            <thead>
                <tr>
                    <th>Page</th>
                    <th># Instances</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $result): ?>
                    <?php $page = $result->getPage() ?>
                    <tr>
                        <td><a href="<?php echo $page->uri ?>"><?php echo $page->uri ?></a></td>
                        <td><?php echo $result->num_instances;?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($previous_page = $context->getPreviousPageURL()): ?>
            <a href="<?php echo $previous_page ?>" class="button wdn-button">Previous Page</a>
        <?php endif; ?>
        
        <?php if ($next_page = $context->getNextPageURL()): ?>
            <a href="<?php echo $next_page ?>" class="button wdn-button">Next Page</a>
        <?php endif; ?>
    <?php endif ?>
<?php endif; ?>
