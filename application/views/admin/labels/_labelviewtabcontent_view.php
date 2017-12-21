<?php
$i = 0;
$first = true;
$sortorderids = '';
$codeids = '';
?>

<div class="tab-content">
    <?php foreach ($lslanguages as $lslanguage): ?>
        <div id='neweditlblset<?php echo $i ?>' class="tab-pane fade in <?php if($i==0){ echo 'active first';} else {echo "not_first";}?>">
            <input type='hidden' class='lslanguage' value='<?php echo $lslanguage ?>' <?php if($i==0){ echo 'id="lslanguagemain"';}?> />
            <table class='answertable table'>
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

                    <?php foreach ($results[$i] as $row): ?>
                        <?php
                        $sortorderids = $sortorderids . ' ' . $row['language'] . '_' . $row['sortorder'];
                        if ($first)
                        {
                            $codeids = $codeids . ' ' . $row['sortorder'];
                        }
                        ?>

                        <tr class="labelDatas" style='white-space: nowrap;' id='row_<?php echo $row['language']; ?>_<?php echo $row['sortorder'] ?>'>
                            <?php if (!$first):?>
                                <td><?php echo $row['code'] ?></td><td><?php echo $row['assessment_value'] ?></td>
                                <?php else:?>
                                <td>
                                    <span class="fa fa-bars bigIcons text-success"></span>
                                </td>

                                <td>
                                    <input type='hidden' class='hiddencode' value='<?php echo $row['code'] ?>' />
                                    <input type='text'  class='codeval  form-control  ' id='code_<?php echo $row['sortorder'] ?>' name='code_<?php echo $row['sortorder'] ?>' maxlength='5' size='6' value='<?php echo $row['code'] ?>'/>
                                </td>

                                <td>
                                    <input type="number" class='assessmentval  form-control  ' id='assessmentvalue_<?php echo $row['sortorder'] ?>' style='text-align: right;' name='assessmentvalue_<?php echo $row['sortorder'] ?>' maxlength='5' size='6' value='<?php echo $row['assessment_value'] ?>' />
                                </td>
                                <?php endif;?>
                            <td>
                            <div class="input-group">
                                <input type='text' class=" form-control  " name='title_<?php echo $row['language'] ?>_<?php echo $row['sortorder'] ?>' maxlength='3000' size='80' value="<?php echo HTMLEscape($row['title']) ?>" />
                                <span class="input-group-addon">
                                    <?php  echo getEditor("editlabel", "title_{$row['language']}_{$row['sortorder']}", "[" . gT("Label:", "js") . "](" . $row['language'] . ")", '', '', '', $action); ?>
                                </span>
                            </div>
                                
                            </td>

                            <td style='text-align:center;'>
                            &nbsp;&nbsp;
                                <?php if ($first && Permission::model()->hasGlobalPermission('labelsets','update')):?>
                                    <button class="btn btn-default btn-sm btnaddanswer"><i class="icon-add  text-success"></i> </button> <?php // eT("Insert a new label after this one") ?>
                                    <button class="btn btn-default btn-sm btndelanswer"><i class="fa fa-trash  text-warning "></i> </button> <?php //eT("Delete this label") ?>
                                    <?php endif;?>
                            </td>
                        </tr>
                        <?php  $position++; ?>
                    <?php  endforeach; ?>
                </tbody>
            </table>

            
            <div class="action-buttons">
            <?php $i++;
            if (Permission::model()->hasGlobalPermission('labelsets','update'))
            { ?>
                    <button type="button" id='btnquickadd_<?php echo $i ?>' class="btnquickadd btn btn-default " data-toggle="modal" data-target="#quickadd">
                        <?php eT('Quick add...') ?>
                    </button>
                <?php }; ?>
                <input type='submit' class="btn btn-success" name='method' value='<?php eT("Save changes") ?>'  id='saveallbtn_<?php echo $lslanguage ?>' />
                </div>
        </div>
        <?php  $first=false;
        endforeach;?>
</div>
