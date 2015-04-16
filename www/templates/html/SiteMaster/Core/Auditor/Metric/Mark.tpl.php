<dl class="fix-mark-details">
    <dt>Mark Name</dt>
    <dd><?php echo $context->name; ?></dd>

    <?php
    if (!empty($context->description)) {
        ?>
        <dt>Description</dt>
        <dd><?php echo $context->description ?></dd>
    <?php
    }

    $help_text = $context->getRawObject()->getHelpText();
    if (!empty($context->help_text) || !empty($help_text)) {
        ?>
        <dt>Suggested Fix</dt>
        <dd>
            <?php
            if (!empty($context->help_text)) {
                echo \Michelf\MarkdownExtra::defaultTransform($context->help_text);
            }

            if (!empty($help_text)) {
                echo \Michelf\MarkdownExtra::defaultTransform($help_text);
            }
            ?>
        </dd>
    <?php
    }
    ?>
</dl>
