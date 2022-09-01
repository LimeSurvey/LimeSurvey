<?php
/* @var $basePermissions array the base permissions a user could have */
/* @var $userCreatePermission bool true if current user has permission to set survey permission for other users */
/* @var $surveyid int */

?>
<div id='edit-permission' class='side-body  <?= getSideBodyClass(false) ?> "'>
    <?php echo viewHelper::getViewTestTag('surveyPermissions'); ?>
    <h3> <?= gT("Survey permissions") ?> </h3>

    <div class="row">
        <div class="col-lg-12 content-right">
            <?php
            $this->renderPartial('_overview_table', [
                'basePermissions' => $basePermissions
            ]);

            if ($userCreatePermission) { //only show adduser and addusergroup buttons if permission is set
                echo CHtml::form(
                    array("admin/surveypermission/sa/adduser/surveyid/{$surveyid}"),
                    'post',
                    array('class' => "form44")
                ); ?>
                <br/><br/>
                <ul class='list-unstyled'>
                    <li>
                        <label class='col-sm-1 col-md-offset-2 text-right control-label' for='uidselect'>
                            <?= gT("User") ?>:
                        </label>
                        <div class='col-sm-4'>
                            <select id='uidselect' name='uid' class='form-control'>
                                <?php echo getSurveyUserList(false, $surveyid); ?>
                            </select>
                        </div>
                        <input style='width: 15em;' class='btn btn-default' type='submit' value='<?= gT("Add user") ?>'
                               onclick="if (document.getElementById('uidselect').value === -1) {
                                   alert( <?= gT('Please select a user first', 'js') ?>);
                                   return false;
                                   }"/>
                        <input type='hidden' name='action' value='addsurveysecurity'/>
                    </li>
                </ul>
                </form>

                <?php
                echo CHtml::form(
                    array("admin/surveypermission/sa/addusergroup/surveyid/{$surveyid}"),
                    'post',
                    array('class' => "form44")
                ); ?>
                <ul class='list-unstyled'>
                    <li>
                        <label class='col-sm-1 col-md-offset-2  text-right control-label' for='ugidselect'>
                            <?= gT("User group") ?>:
                        </label>
                        <div class='col-sm-4'>
                            <select id='ugidselect' name='ugid' class='form-control'>
                                <?php echo getSurveyUserGroupList('htmloptions', $surveyid); ?>
                            </select>
                        </div>
                        <input style='width: 15em;' class='btn btn-default' type='submit'
                               value='<?= gT("Add group users") ?>'
                               onclick="if (document.getElementById('ugidselect').value == -1) {
                                   alert(<?= gT("Please select a user group first", "js") ?>);
                                   return false;
                                   }"/>
                        <input type='hidden' name='action' value='addusergroupsurveysecurity'/>
                    </li>
                </ul>
                </form>
            <?php }
            ?>

        </div>
    </div>
</div>
