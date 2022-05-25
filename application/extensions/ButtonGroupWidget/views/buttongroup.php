<?php
/**
 * @var string $ariaLabel
 * @var string $name
 * @var string $id
 *
 * @var array $selectOptions [$value => $caption]
 * @var string $checkedOption
 */
?>

<div class="btn-group <?= $htmlOptions['class'] ?? '' ?>" id="<?= $id ?>" role="group" aria-label="<?= $ariaLabel ?? '' ?>" <?= isset($htmlOptions['data-url']) ? "data-url='" . $htmlOptions["data-url"]. "'" : '' ?>>
    <?php $count = 1 ?>
    <?php foreach ($selectOptions as $value => $caption) : ?>
        <input type="radio" class="btn-check" name="<?= $name ?>" id="<?= $id . '_' . $count ?>" autocomplete="off"
               value="<?= $value ?>" <?= $checkedOption == $value ? 'checked' : '' ?>>
        <label class="btn btn-outline-primary" for="<?= $id . '_' . $count ?>"><?= $caption ?></label>
        <?php $count++ ?>
    <?php endforeach; ?>
</div>
