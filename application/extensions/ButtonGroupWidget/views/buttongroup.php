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

<div class="btn-group <?= $htmlOptions['class'] ?? '' ?>" data-bs-toggle="tooltip" title="<?= $htmlOptions['title'] ?? '' ?>"
    style="<?= $htmlOptions['style'] ?? '' ?>"
    id="<?= $id ?>" role="group"
    aria-label="<?= $ariaLabel ?? '' ?>"
    <?= isset($htmlOptions['data-url']) ? "data-url='" . $htmlOptions["data-url"] . "'" : '' ?>>
    <?php $count = 1 ?>
    <?php foreach ($selectOptions as $value => $caption) : ?>
        <input type="radio" class="btn-check" name="<?= $name ?>" id="<?= $id . '_' . $count ?>" autocomplete="off"
            value="<?= $value ?>" <?= $checkedOption == $value ? 'checked' : '' ?> <?= isset($htmlOptions['disabled']) && $htmlOptions['disabled'] ? 'disabled' : '' ?>>
        <label class="btn btn-outline-secondary" for="<?= $id . '_' . $count ?>">
            <?php if (isset($htmlOptions['icon']) &&  $htmlOptions['icon']): ?>
                <span class="<?= $htmlOptions['icon'][$value] ?>" style="margin-right: 5px;"></span>
            <?php endif; ?>
            <?= $caption ?>
        </label>
        <?php $count++ ?>
    <?php endforeach; ?>
</div>