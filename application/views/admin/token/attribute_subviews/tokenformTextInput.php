<?php
/** @var array $attrDescription */
/** @var string $attrName */
/** @var string $inputValue */
?>
<input
    class='form-control<?= $attrDescription['mandatory'] == 'Y' ? ' mandatory-attribute' : '' ?>'
    type='text'
    size='55'
    id='<?php echo $attrName; ?>'
    name='<?php echo $attrName; ?>'
    value='<?php if (isset($inputValue)) {
        echo htmlspecialchars((string) $inputValue, ENT_QUOTES, 'utf-8');
    } ?>'
/>
