<div class="col-lg-12 list-surveys">
    <div class="pagetitle h3"><?php eT("User group"); ?> : <?php echo $groupname; ?></div>

    <div class="row">
        <div class="col-lg-12 content-right text-center">
            <div class="h4"><?php eT("Group members"); ?></div>

            <?php if(isset($groupfound)):?>
                <strong><?php eT("Group description: ");?></strong>
                <?php echo htmlspecialchars($usergroupdescription);?>

            <?php endif;?>

<br/><br/>




            <?php if(isset($headercfg)): ?>
                <?php if($headercfg["type"] == "success"): ?>

                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <?php echo $headercfg["message"];?>
                    </div>

                <?php else:?>

                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <?php echo $headercfg["message"];?>
                    </div>

                <?php endif;?>
            <?php endif;?>

            <br/><br/>
            <?php if (!empty($userloop)) { ?>
                <table class='users table'>
                    <thead><tr>
                        <th style="text-align: center;"><?php eT("Action");?></th>
                        <th style="text-align: center;"><?php eT("Username");?></th>
                        <th style="text-align: center;"><?php eT("Email");?></th>
                        </tr></thead>
                    <tbody>
                    <?php
                    foreach ($userloop as $currentuser)
                    {
                        ?>
                        <tr class='<?php echo $currentuser["rowclass"];?>'>
                            <td align='center'>
                            <?php
                            if(isset($currentuser["displayactions"]) && $currentuser["displayactions"] == true && $currentuser["userid"] != '1')
                            { ?>
                                <?php echo CHtml::form(array("admin/usergroups/sa/user/ugid/{$ugid}/action/remove"), 'post'); ?>
                                    <button  data-toggle="tooltip" data-placement="bottom" title="<?php eT('Delete');?>" type="submit" onclick='return confirm("<?php eT("Are you sure you want to delete this entry?","js");?>")' class="btn btn-default btn-xs ">
                                        <span class="fa fa-trash text-warning"></span>
                                    </button>
                                    <input name='uid' type='hidden' value='<?php echo $currentuser["userid"]; ?>' />
                                </form>
                                <?php
                            }
                            else
                            {
                                ?>
                                &nbsp;
                            <?php
                            }
                            ?>
                            </td>
                            <td align='center'><?php echo \CHtml::encode($currentuser["username"]);?></td>
                            <td align='center'><?php echo \CHtml::encode($currentuser["email"]);?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            <?php } ?>

            <?php
            if (!empty($useradddialog))
            {
                ?>
                    <?php echo CHtml::form(array("admin/usergroups/sa/user/ugid/{$ugid}/action/add"), 'post'); ?>
                        <table class='users'>
                            <tbody>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td align='center'>
                                        <div class="row">
                                            <div class="col-lg-8">
                                                <select name='uid' class="form-control col-lg-4">
                                                    <?php echo $useraddusers; ?>
                                                </select>
                                            </div>
                                            <div class="col-lg-4">
                                                <input type='submit' value='<?php eT("Add user"); ?>' class="btn btn-default"/>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                <?php
            }
            ?>
        </div>
    </div>
</div>
