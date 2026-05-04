<?php
$tabs = emailtemplates::getTabTypeArray($surveyid);
?>
<div id="tab-<?= CHtml::encode($grouplang) ?>" class="tab-pane fade <?= CHtml::encode($active) ?>" role="tabpanel" aria-labelledby="tab-lang-<?= CHtml::encode($grouplang) ?>">
    <ul class="nav nav-tabs" role="tablist">
        <?php $count = 0;
        $state = 'active'; ?>
        <?php foreach ($tabs as $tab => $details): ?>
            <?php $tabId = 'tab-' . CHtml::encode($grouplang) . '-' . CHtml::encode($tab) . '-tab'; ?>
            <li role="presentation" class="nav-item">
                <a class="nav-link <?= $state ?>" id="<?= $tabId ?>" role="tab" aria-selected="<?= $state === 'active' ? 'true' : 'false' ?>" aria-controls="tab-<?= $grouplang ?>-<?= $tab ?>" data-bs-toggle="tab" href="#tab-<?= $grouplang ?>-<?= $tab ?>">
                    <?= $details['title'] ?>
                </a>
            </li>
            <?php if ($count == 0) {
                $state = '';
                $count++;
            } ?>
        <?php endforeach; ?>
    </ul>
    <div class="tab-content tabsinner" id='tabsinner-<?php echo $grouplang; ?>'>
        <?php
        $count = 0;
        $active = 'show active';
        foreach ($tabs as $tab => $details) {
            $this->renderPartial('/admin/emailtemplates/email_language_template_tab', compact('ishtml', 'surveyid', 'esrow', 'grouplang', 'tab', 'details', 'active'));
            if ($count == 0) {
                $active = '';
                $count++;
            }
        }
        ?>
    </div>
</div>
