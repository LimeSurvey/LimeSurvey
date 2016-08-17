<h3 class="pagetitle"><?php eT('Edit template permissions');?></h3>
<div class="row" style="margin-bottom: 100px">
    <div class="col-lg-6 col-lg-offset-3 content-right">

        <?php echo CHtml::form(array("admin/user/sa/usertemplates"), 'post', array('name'=>'modtemplaterightsform', 'id'=>'modtemplaterightsform')); ?>
        <table id="user-template-permissions" class="table table-striped activecell" style="margin:0 auto;">
            <thead>
                <tr>
                    <th colspan="2" class="header">
                        <?php eT('Set templates that this user may access');?>: <?php echo htmlspecialchars($postuser);?>
                    </th>
                </tr>
                <tr>
                    <th>
                        <?php eT('Template name');?>
                    </th>
                    <th>
                        <?php eT('Access');?>
                    </th>
                </tr>
                <tr>
                    <th>
                        &nbsp;
                    </th>
                    <th>
                        <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                            'name' => 'alltemplates',
                            'id'=>'alltemplates',
                            'value' => 0,
                            'onLabel'=>gT('On'),
                            'offLabel' => gT('Off')));
                        ?>
                    </th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td colspan="2">
                        <input type="submit" class="hidden" value="<?php eT('Save settings');?>" />
                        <input type="hidden" name="action" value="usertemplates" />
                        <input type="hidden" name="uid" value="<?php echo $postuserid;?>" />
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
                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                                'name' => $trow['folder'].'_use',
                                'id'=>$trow['folder'].'_use',
                                'value' => isset($templaterights[$trow['folder']]['use'])?$templaterights[$trow['folder']]['use']:0,
                                'onLabel'=>gT('On'),
                                'offLabel' => gT('Off')));
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
            </tbody>
        </table>
        </form>


    </div>
</div>

