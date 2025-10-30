<?php
/** @var string $tooltip */
/** @var string $linkId */
/** @var bool $enabledCondition */
/** @var string $linkClass */
/** @var string $url */
/** @var array $linkAttributes */
/** @var string $iconClass */
/** @var string $title */
?>

<div data-bs-toggle="tooltip" title="<?= $tooltip ?? '' ?>">
    <a id="<?= $linkId ?? '' ?>"
       class="dropdown-item <?= $enabledCondition ? "" : "disabled" ?> <?= $linkClass ?? '' ?>"
       href="<?= $url ?? '#' ?>"
       role="button"
        <?php if (isset($linkAttributes) && is_array($linkAttributes)) : ?>
            <?php foreach ($linkAttributes as $attribute => $value) : ?>
                <?= "$attribute='$value'" ?>
            <?php endforeach; ?>
        <?php endif; ?>>
        <?php if (isset($iconClass)) : ?>
            <i class="<?= $iconClass ?>"></i>
        <?php endif; ?>
        <?= $title ?>
    </a>
</div>
