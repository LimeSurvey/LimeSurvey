<div class="pagetitle h3"><?php printf(gT('Edit theme permissions for user %s'),"<em>".\CHtml::encode($oUser->users_name)."</em>");?></div>
<div class="row">
    <div class="col-12">
        <p><?php eT("If the user doesn't have global view/read global permission for themes, please select the themes he should be able to use for surveys."); ?></p>
    </div>
</div>
<div class="row" style="margin-bottom: 100px">
    <div class="col-xl-6 offset-xl-3 content-right">

        <?php echo CHtml::form(array("admin/user/sa/usertemplates"), 'post', array('name'=>'modtemplaterightsform', 'id'=>'modtemplaterightsform')); ?>
        <table id="user-template-permissions" class="table table-striped activecell" style="margin:0 auto;">
            <thead>
                <tr>
                    <th>
                        <?php eT('Theme name');?>
                    </th>
                    <th>
                        <?php eT('Available for surveys');?>
                    </th>
                </tr>
                <tr>
                    <th>
                        <?php eT('All themes');?>
                    </th>
                    <th>
                        <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                            'name'          => 'alltemplates',
                            'checkedOption' => 0,
                            'selectOptions' => [
                                '1' => gT('On'),
                                '0' => gT('Off'),
                            ]
                        ]); ?>
                    </th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="2">
                        <input type="submit" class="d-none" value="<?php eT('Save settings');?>" />
                        <input type="hidden" name="action" value="usertemplates" />
                        <input type="hidden" name="uid" value="<?php echo $oUser->uid;?>" />
                    </td>
                </tr>
            </tfoot>

            <tbody>

                <?php
                $templaterights=$data['templaterights'];
                $table_row_odd_even = 'odd';
                foreach ($data['templates'] as $trow)
                {?>

                    <tr>
                        <td><?php echo $trow["folder"];?></td>
                        <td>
                            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                'name' => $trow['folder'] . '_use',
                                'checkedOption' => $templaterights[$trow['folder']]['use'] ?? 0,
                                'value' => $templaterights[$trow['folder']]['use'] ?? 0,
                                'selectOptions' => [
                                    '1' => gT('On'),
                                    '0' => gT('Off'),
                                ]
                            ]); ?>
                        </td>
                    </tr>
                    <?php } ?>
            </tbody>
        </table>
        </form>

    </div>
</div>
