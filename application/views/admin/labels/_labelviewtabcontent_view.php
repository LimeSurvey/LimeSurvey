<?php
$i = 0;
$first = true;
$sortorderids = '';
$codeids = '';
?>

<div class="tab-content">
    <?php foreach ($lslanguages as $lslanguage) : ?>
        <div id='neweditlblset<?php echo $i ?>' class="table-responsive tab-pane lang-<?= $lslanguage ?> <?= $i === 0 ? "active show first" : "not_first" ?>">
            <input type='hidden' class='lslanguage' value='<?= $lslanguage ?>' <?= $i === 0 ? 'id="lslanguagemain"' : '' ?>/>
            <table class='answertable table table-hover'>
                <thead>
                <tr>
                    <?php if ($first): ?>
                        <th><?php eT('Position'); ?></th>
                    <?php endif; ?>
                    <th><?php eT("Code") ?></th>
                    <th><?php eT("Assessment value") ?></th>
                    <th><?php eT("Title") ?></th>
                    <th><?php eT("Action") ?></th>
                </tr>
                </thead>

                <tbody>
                <?php $position = 0;
                $alternate = false; ?>

                    <?php foreach ($results as $row): ?>
                        <?php
                        $sortorderids = $sortorderids . ' ' . $lslanguage . '_' . $row['sortorder'];
                        if ($first)
                        {
                            $codeids = $codeids . ' ' . $row['sortorder'];
                        }
                        ?>
                        <?= Yii::app()->twigRenderer->renderViewFromFile(
                            '/application/views/admin/labels/labelRow.twig',
                            [
                                'language' => $lslanguage,
                                'first' => $first,
                                'rowId' => $row['sortorder'],
                                'code' => $row['code'],
                                'assessmentValue' => $row['assessment_value'],
                                'title' => array_key_exists($lslanguage, $row->labell10ns) ? $row->labell10ns[$lslanguage]->title : '',
                                'hasLabelSetUpdatePermission' => $updatePermission,
                            ],
                            true
                        ); ?>
                        <?php  $position++; ?>
                    <?php  endforeach; ?>
                </tbody>
            </table>

            <!-- Action Buttons Quick Add and Save Changes -->
            <div class="action-buttons text-end">
                <?php $i++;
                if ($updatePermission) : ?>
                    <button type="button" id='btnquickadd_<?php echo $i ?>' class="btnquickadd btn btn-outline-secondary " data-bs-toggle="modal"
                            data-bs-target="#quickadd">
                        <?php eT('Quick add labels') ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php $first = false; ?>
    <?php endforeach ?>
</div>
