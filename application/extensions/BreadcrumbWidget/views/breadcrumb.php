<?php
/** @var  $breadcrumbs  array */

/** @var  $extraClass  string */
/** @var  $htmlOptions  array */
?>
<?= CHtml::tag('div', $htmlOptions) ?>
<nav aria-label="<?= gT("Breadcrumb") ?>">
    <ol class="breadcrumb ls-flex-row align-items-center align-content-flex-start <?= $extraClass ?>">
        <?php foreach ($breadcrumbs as $i => $breadcrumbArray) : ?>
            <?php
            $id = array_key_exists('id', $breadcrumbArray) ? $breadcrumbArray['id'] : '';
            $href = array_key_exists('href', $breadcrumbArray) ? $breadcrumbArray['href'] : '';
            $text = $breadcrumbArray['text'];
            $lastOne = count($breadcrumbs) === $i + 1;
            ?>
            <li class="breadcrumb-item <?= $lastOne ? 'active' : '' ?>" <?= $lastOne ? 'aria-current="page"' : '' ?> data-bs-toggle="tooltip"
                title="<?= CHtml::encode($breadcrumbArray['fullText']) ?>">
                <?php if ($href !== '') : ?>
                    <a id="<?= $id ?>" class="pjax animate"
                       href="<?= $href ?>">
                        <?= $text ?>
                    </a>
                <?php else : ?>
                    <?= $text ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
<?= CHtml::closeTag('div') ?>

<input type="hidden" id="gettheuserid" value="<?= App()->user->id ?>"/>
