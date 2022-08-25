<div class="col-lg-12 list-surveys">
    <div class="row">
        <div class="col-lg-12 content-right">
            <div class="h4"><?php eT("Group members"); ?></div>

            <?php if (isset($groupfound)) : ?>
                <strong><?php eT("Group description: "); ?></strong>
                <?php echo htmlspecialchars($usergroupdescription); ?>

            <?php endif; ?>

            <br/><br/>

            <?php if (isset($headercfg)) : ?>
                <?php if ($headercfg["type"] === "success") : ?>
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <?php echo $headercfg["message"]; ?>
                    </div>

                <?php else : ?>
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <?php echo $headercfg["message"]; ?>
                    </div>

                <?php endif; ?>
            <?php endif; ?>

            <br/><br/>
            <?php if (!empty($userloop)) { ?>
                <div class="table-responsive">
                    <table class='users table table-hover'>
                        <thead>
                        <tr>
                            <th><?php eT("Action"); ?></th>
                            <th><?php eT("Username"); ?></th>
                            <th><?php eT("Email"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($userloop as $currentuser) {
                            ?>
                            <tr class='<?php echo $currentuser["rowclass"]; ?>'>
                                <td>
                                    <div class="icon-btn-row">
                                        <?php
                                        if ((isset($currentuser["displayactions"]) && $currentuser["displayactions"] == true || Permission::model()->hasGlobalPermission('superadmin')) && $currentuser["userid"] != '1') { ?>
                                            <?php echo CHtml::form(["userGroup/DeleteUserFromGroup/ugid/{$ugid}/"], 'post'); ?>
                                            <button
                                                class="btn btn-default btn-sm"
                                                data-toggle="tooltip"
                                                data-placement="bottom"
                                                title="<?php eT('Delete'); ?>"
                                                type="submit"
                                                onclick='return confirm("<?php eT("Are you sure you want to delete this entry?", "js"); ?>")'>
                                                <span class="fa fa-trash text-danger"></span>
                                            </button>
                                            <input name='uid' type='hidden' value='<?php echo $currentuser["userid"]; ?>'/>
                                            <?php echo CHtml::endForm() ?>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td><?php echo \CHtml::encode($currentuser["username"]); ?></td>
                                <td><?php echo \CHtml::encode($currentuser["email"]); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

            <?php
            if (!empty($useradddialog)) {
                ?>
                <?php echo CHtml::form(["userGroup/AddUserToGroup"], 'post'); ?>
                <table class='users'>
                    <tbody>
                    <tr>
                        <td>
                            <div class="row">
                                <div class="col-lg-8">
                                    <?php echo CHtml::dropDownList('uid', '-1', $addableUsers, ['class' => "form-control col-lg-4"]); ?>
                                    <input name='ugid' type='hidden' value='<?php echo $ugid; ?>'/>
                                </div>
                                <div class="col-lg-4">
                                    <input type='submit' value='<?php eT("Add user"); ?>' class="btn btn-default"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <?php echo CHtml::endForm() ?>
                <?php
            }
            ?>
        </div>
    </div>
</div>
