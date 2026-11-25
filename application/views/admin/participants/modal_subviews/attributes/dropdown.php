<?php
/**
 * Drop-down list of a custom attribute in the edit participant modal
 *
 * @var string $name Column name of the attribute, e.g. ea_12
 * @var string $localizedname Attribute name in the admin language
 * @var string $value Value stored for the participant, empty if there is none
 * @var array<int, array<string, mixed>> $options Rows of the drop-down options of the attribute
 */

$selectId = 'Attributes_' . CHtml::encode($name);
$storedValue = (string) $value;
$optionValues = array_map('strval', array_column($options, 'value'));
// A stored value can be missing from the list after its option was renamed or deleted:
// It is offered as an extra option, otherwise saving the participant would empty it
$isStoredValueMissing = $storedValue !== '' && !in_array($storedValue, $optionValues, true);
?>
<label id="<?= $selectId ?>_label" class="form-label" for="<?= $selectId ?>"><?= CHtml::encode($localizedname) ?></label>
<div class='mb-3'>
    <select class="form-select" name="Attributes[<?= CHtml::encode($name) ?>]" id="<?= $selectId ?>">
        <option></option>  <!-- Nothing selected -->
        <?php foreach ($optionValues as $optionValue) : ?>
            <option
                <?= $optionValue === $storedValue ? 'selected' : '' ?>
                value='<?= CHtml::encode($optionValue) ?>'
            >
                <?= CHtml::encode($optionValue) ?>
            </option>
        <?php endforeach; ?>
        <?php if ($isStoredValueMissing) : ?>
            <option selected value='<?= CHtml::encode($storedValue) ?>'>
                <?= CHtml::encode(sprintf(gT('%s (no longer in the list)'), $storedValue)) ?>
            </option>
        <?php endif; ?>
    </select>
</div>
