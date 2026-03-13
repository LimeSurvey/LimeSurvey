<?php
/** @var  array $breadcrumbs */
/** @var  string $extraClass */
/** @var  array $htmlOptions */
$breadcrumbTrail = implode(', ', array_map(function ($b) {
    return strip_tags($b['text']);
}, $breadcrumbs));
$ariaLabel = $breadcrumbTrail !== '' ? gT("Breadcrumb") . ': ' . $breadcrumbTrail : gT("Breadcrumb");
?>
<?= CHtml::tag('div', $htmlOptions) ?>
<nav aria-label="<?= CHtml::encode($ariaLabel) ?>">
    <ol class="breadcrumb ls-flex-row align-items-center align-content-flex-start <?= $extraClass ?>">
        <?php foreach ($breadcrumbs as $i => $breadcrumbArray) : ?>
            <?php
            $id = array_key_exists('id', $breadcrumbArray) ? $breadcrumbArray['id'] : '';
            $href = array_key_exists('href', $breadcrumbArray) ? $breadcrumbArray['href'] : '';
            $text = $breadcrumbArray['text'];
            $title = array_key_exists('title', $breadcrumbArray) ? $breadcrumbArray['title'] : '';
            $lastOne = count($breadcrumbs) === $i + 1;
            $textPlain = strip_tags($text);
            ?>
            <li class="breadcrumb-item <?= $lastOne ? 'active' : '' ?>" <?= $lastOne ? 'aria-current="page"' : '' ?> data-bs-toggle="tooltip"
                title="<?= CHtml::encode($breadcrumbArray['fullText']) ?>">
                <?php if ($href !== '') : ?>
                    <a id="<?= $id ?>" class="pjax animate"
                       href="<?= $href ?>"
                       <?= !empty($title) ? 'title="' . $title . '"' : '' ?>>
                        <?= $text ?>
                    </a>
                <?php else : ?>
                    <?php if ($lastOne && $textPlain !== '') : ?>
                        <span class="visually-hidden"><?= gT("Current page:") ?> </span>
                    <?php endif; ?>
                    <?= $text ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
<?= CHtml::closeTag('div') ?>

<input type="hidden" id="gettheuserid" value="<?= App()->user->id ?>"/>
