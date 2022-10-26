<?php
$i = 0;
$first = true;
$sortorderids = '';
$codeids = '';
?>

<div class="tab-content">
    <?php foreach ($lslanguages as $lslanguage): ?>
        <div id='neweditlblset<?php echo $i ?>' class="table-responsive tab-pane fade in lang-<?= $lslanguage ?> <?php if($i==0){ echo 'active first';} else {echo "not_first";}?>">
            <input type='hidden' class='lslanguage' value='<?php echo $lslanguage ?>' <?php if($i==0){ echo 'id="lslanguagemain"';}?> />
            <table class='answertable table table-hover'>
                <thead>
                    <tr>
                        <?php if ($first): ?>
                            <th><?php eT('Position');?></th>
                            <?php endif;?>
                        <th><?php eT("Code") ?></th>
                        <th><?php eT("Assessment value") ?></th>
                        <th><?php eT("Title") ?></th>
                        <th><?php eT("Action") ?></th>
                    </tr>
                </thead>

                <tbody>
                    <?php $position = 0; $alternate = false; ?>

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
                                'hasLabelSetUpdatePermission' => Permission::model()->hasGlobalPermission('labelsets', 'update'),
                            ],
                            true
                        ); ?>
                        <?php  $position++; ?>
                    <?php  endforeach; ?>
                </tbody>
            </table>

            <!-- Action Buttons Quick Add and Save Changes -->
            <div class="action-buttons text-right">
                <?php $i++; ?>
                <?php if (Permission::model()->hasGlobalPermission('labelsets','update')): ?>
                    <button type="button" id='btnquickadd_<?php echo $i ?>' class="btnquickadd btn btn-default " data-toggle="modal" data-target="#quickadd">
                        <?php eT('Quick add labels') ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php  $first=false;
        endforeach;?>
</div>
