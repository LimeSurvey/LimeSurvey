<script type="text/javascript">
    var sImageURL = ''; // in 2.06, used to display the icon in jQgrid
    var duplicatelabelcode='<?php eT('Error: You are trying to use duplicate label codes.','js'); ?>';
    var otherisreserved='<?php eT("Error: 'other' is a reserved keyword.",'js'); ?>';
    var quickaddtitle='<?php eT('Quick-add subquestion or answer items','js'); ?>';
</script>

<!-- Label Bar menu -->
<div class='menubar' id="labelbar">
    <div class='row container-fluid'>

        <div class="col-lg-6">

            <!-- View buttons -->
            <?php if (isset($labelbar['buttons']['view'])):?>
                <!-- Add -->
                <a class="btn btn-default" href="<?php echo $this->createUrl("admin/labels/sa/newlabelset");?>" role="button">
                    <span class="icon-add text-success"></span>
                    <?php eT("Create or import new label set(s)"); ?>
                </a>


                <!-- Export Multiple -->
                <?php if ( count($labelsets) > 0 ): ?>
                    <?php if (Permission::model()->hasGlobalPermission('labelsets','export')):?>
                        <a class="btn btn-default" href="<?php echo $this->createUrl("admin/labels/sa/exportmulti");?>" role="button">
                            <span class="icon-export text-success"></span>
                            <?php eT("Export multiple label sets"); ?>
                        </a>
                    <?php endif; ?>
                <?php else:?>
                    <?php if (Permission::model()->hasGlobalPermission('labelsets','export')):?>
                        <span title="<?php eT("No label sets available"); ?>" data-toggle="tooltip" data-placement="bottom" style="display: inline-block">
                            <a class="btn btn-default disabled" role="button" >
                                <span class="icon-export text-success"></span>
                                <?php eT("Export multiple label sets"); ?>
                            </a>
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>


            <!-- Edition buttons -->

             <?php if (isset($labelbar['buttons']['edition'])):?>

                 <?php if (isset($labelbar['buttons']['edit'])): ?>
                     <!-- Edit label set -->
                     <?php if (Permission::model()->hasGlobalPermission('labelsets','update')):?>
                         <a class="btn btn-default" href="<?php echo $this->createUrl("admin/labels/sa/editlabelset/lid/".$lid);?>" role="button">
                             <span class="glyphicon glyphicon-pencil  text-success"></span>
                             <?php eT("Edit label set"); ?>
                         </a>
                     <?php endif; ?>


                     <!-- Export this label set -->
                     <?php if (Permission::model()->hasGlobalPermission('labelsets','export')):?>
                         <a class="btn btn-default" href="<?php echo $this->createUrl("admin/export/sa/dumplabel/lid/$lid");?>" role="button">
                             <span class="icon-export text-success"></span>
                             <?php eT("Export this label set"); ?>
                         </a>
                     <?php endif; ?>
                 <?php endif; ?>


                 <!-- Delete label set -->
                <?php if (isset($labelbar['buttons']['delete']) && $labelbar['buttons']['delete'] == true ): ?>
                    <?php if (Permission::model()->hasGlobalPermission('labelsets','delete')): ?>
                        <a class="btn btn-default" role="button" data-action='deletelabelset' data-url='<?php echo $this->createUrl("admin/labels/sa/process"); ?>' data-confirm='<?php eT('Do you really want to delete this label set?'); ?>' >
                            <span class="glyphicon glyphicon-trash  text-warning"></span>
                            <?php eT("Delete label set"); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

             <?php endif;?>

        </div>


        <!-- Right action buttons -->
        <div class="col-lg-6 text-right">

            <!-- view action buttons-->
            <?php if (isset($labelbar['buttons']['view'])):?>

                <!-- return to admin pannel -->
                <a class="btn btn-default pull-right" href="<?php echo $this->createUrl('admin/index'); ?>" role="button" style="display: block">
                    <span class="glyphicon glyphicon-backward"></span>
                    &nbsp;&nbsp;
                    <?php eT('Return to admin panel'); ?>
                </a>

                <!-- labelsetchanger -->
                <div class="form-group form-inline col-md-6 pull-right">
                    <label for='labelsetchanger'><?php eT("Label sets:");?> </label>
                    <select id='labelsetchanger' onchange="window.open(this.options[this.selectedIndex].value,'_top')" class="form-control">
                        <option value=''
                            <?php if (!isset($lid) || $lid<1) { ?> selected='selected' <?php } ?>
                            ><?php eT("Please choose..."); ?></option>

                        <?php if (count($labelsets)>0)
                            {
                                foreach ($labelsets as $lb)
                                { ?>
                                <option data-labelset-id='<?php echo $lb[0]; ?>' value='<?php echo $this->createUrl("admin/labels/sa/view/lid/".$lb[0]); ?>'
                                    <?php if ($lb[0] == $lid) { ?> selected='selected' <?php } ?>
                                    ><?php echo htmlspecialchars($lb[1],ENT_QUOTES); ?></option>
                                <?php }
                        } ?>
                    </select>
                </div>
            <?php endif; ?>

            <!-- edition action buttons -->
            <?php if (isset($labelbar['buttons']['edition'])):?>
                <a class="btn btn-success" href="#" role="button" id="save-form-button" data-form-id="<?php echo $labelbar['savebutton']['form']; ?>">
                    <span class="glyphicon glyphicon-ok"></span>

                    <?php echo $labelbar['savebutton']['text'];?>
                </a>
                <a class="btn btn-danger" href="<?php echo $labelbar['closebutton']['url']; ?>" role="button">
                    <span class="glyphicon glyphicon-close" ></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>
        </div>
    </div>
</div>
