<?php
$i = 0;
$first = true;
$sortorderids = '';
$codeids = '';
?>

<div class="tab-content">
    <?php foreach ($lslanguages as $lslanguage) : ?>
        <div id='neweditlblset<?php echo $i ?>' class="table-responsive tab-pane <?= $i === 0 ? "active show first" : "not_first" ?>">
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
                    if ($first) {
                        $codeids = $codeids . ' ' . $row['sortorder'];
                    }
                    ?>

                    <tr class="labelDatas" style='white-space: nowrap;' id='row_<?php echo $lslanguage; ?>_<?php echo $row['sortorder'] ?>'>
                        <?php if (!$first) : ?>
                            <td><?php echo $row['code'] ?></td>
                            <td><?php echo $row['assessment_value'] ?></td>
                        <?php else : ?>
                            <td>
                                <span class="fa fa-bars bigIcons text-success"></span>
                            </td>

                            <td>
                                <input type='hidden' class='hiddencode' value='<?php echo $row['code'] ?>'/>
                                <input type='text' class='codeval  form-control  ' id='code_<?php echo $row['sortorder'] ?>'
                                       name='code_<?php echo $row['sortorder'] ?>' maxlength='20' size='20' value='<?php echo $row['code'] ?>'/>
                            </td>

                            <td>
                                <input type="number" class='assessmentval  form-control  ' id='assessmentvalue_<?php echo $row['sortorder'] ?>'
                                       style='text-align: right;' name='assessmentvalue_<?php echo $row['sortorder'] ?>' maxlength='5' size='6'
                                       value='<?php echo $row['assessment_value'] ?>'/>
                            </td>
                        <?php endif; ?>

                        <td>
                            <input type='text' class=" form-control  " name='title_<?php echo $lslanguage; ?>_<?php echo $row['sortorder'] ?>'
                                   id='title_<?php echo $lslanguage; ?>_<?php echo $row['sortorder'] ?>' maxlength='3000' size='80' value="<?php
                            if (array_key_exists($lslanguage, $row->labell10ns)) {
                                echo HTMLEscape($row->labell10ns[$lslanguage]->title);
                            } ?>"/>
                        </td>
                        <td>
                            <div class="icon-btn-row">
                                <?php if (Permission::model()->hasGlobalPermission('labelsets', 'update')) : ?>
                                    <a
                                        href='#'
                                        class=" btn btn-outline-secondary btn-sm htmleditor--openmodal"
                                        data-target-field-id="title_<?php echo $lslanguage; ?>_<?php echo $row['sortorder'] ?>"
                                        data-bs-toggle="tooltip"
                                        title="Open editor">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <?php if ($first) : ?>
                                        <button class="btn btn-outline-secondary btn-sm btnaddanswer" data-bs-toggle="tooltip"
                                                title="<?php eT("Add label"); ?>">
                                            <i class="ri-add-circle-fill text-success"></i>
                                        </button> <?php // eT("Insert a new label after this one") ?>
                                        <button class="btn btn-outline-secondary btn-sm btndelanswer" data-bs-toggle="tooltip"
                                                title="<?php eT("Delete label"); ?>">
                                            <i class="fa fa-minus-circle text-danger "></i>
                                        </button> <?php //eT("Delete this label") ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php $position++; ?>
                <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Action Buttons Quick Add and Save Changes -->
            <div class="action-buttons text-end">
                <?php $i++;
                if (Permission::model()->hasGlobalPermission('labelsets', 'update')): ?>
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
