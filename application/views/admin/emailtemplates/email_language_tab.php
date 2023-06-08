<?php
$tabs = emailtemplates::getTabTypeArray($surveyid);
?>
<div id="tab-<?= CHtml::encode($grouplang) ?>" class="tab-pane fade <?= CHtml::encode($active) ?>">
    <ul class="nav nav-tabs">
        <?php $count = 0;
        $state = 'active'; ?>
        <?php foreach ($tabs as $tab => $details): ?>
            <li role='presentation' class='nav-item'>
                <a class="nav-link <?= $state ?>" data-bs-toggle='tab' href='#tab-<?= $grouplang ?>-<?= $tab ?>'>
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
