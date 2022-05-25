<?php
/**
 * @var string $ariaLabel
 * @var string $name
 *
 * @var array $selectOptions [$value => $caption]
 * @var string $checkedOption
 */
?>

<div class="btn-group <?= $htmlOptions['class'] ?? '' ?>" id="<?= $name ?>" role="group" aria-label="<?= $ariaLabel ?? '' ?>">
    <?php $count = 1 ?>
    <?php foreach ($selectOptions as $value => $caption) : ?>
        <input type="radio" class="btn-check" name="<?= $name ?>" id="<?= $name . '_' . $count ?>" autocomplete="off"
               value="<?= $value ?>" <?= $checkedOption == $value ? 'checked' : '' ?>>
        <label class="btn btn-outline-primary" for="<?= $name . '_' . $count ?>"><?= $caption ?></label>
        <?php $count++ ?>
    <?php endforeach; ?>
</div>