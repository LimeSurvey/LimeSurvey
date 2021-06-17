<script type="text/javascript">
    var sImageURL = ''; // in 2.06, used to display the icon in jQgrid, not necessary anymore
    var duplicatelabelcode='<?php eT('Error: You are trying to use duplicate label codes.','js'); ?>';
    var otherisreserved='<?php eT("Error: 'other' is a reserved keyword.",'js'); ?>';
    var quickaddtitle='<?php eT('Quick-add subquestion or answer items','js'); ?>';
</script>

<!-- Label Bar menu -->
<div class='menubar' id="labelbar" style="box-shadow: 3px 3px 3px #35363f; margin-bottom: 10px;">
    <div class='row container-fluid'>

        <div class="col-lg-6" style="margin-bottom: 10px;">

            <!-- View buttons -->
            <?php if (isset($labelbar['buttons']['view'])):?>
                <?php if (Permission::model()->hasGlobalPermission('labelsets','create') || Permission::model()->hasGlobalPermission('labelsets','import')):?>
                    <!-- Create or Import -->
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/labels/sa/newlabelset");?>" role="button" style="margin-top: 10px;">
                        <span class="icon-add text-success"></span>
                        <?php eT("Create or import new label set(s)"); ?>
                    </a>
                    <?php endif; ?>
                <!-- Export Multiple -->
                <?php if ( count($labelsets) > 0 ): ?>
                    <?php if (Permission::model()->hasGlobalPermission('labelsets','export')):?>
                        <a class="btn btn-default" href="<?php echo $this->createUrl("admin/labels/sa/exportmulti");?>" role="button" style="margin-top: 10px;">
                            <span class="icon-export text-success"></span>
                            <?php eT("Export multiple label sets"); ?>
                        </a>
                        <?php endif; ?>
                    <?php else:?>
                    <?php if (Permission::model()->hasGlobalPermission('labelsets','export')):?>
                        <span title="<?php eT("No label sets available"); ?>" data-toggle="tooltip" data-placement="bottom" style="display: inline-block">
                            <a class="btn btn-default disabled" role="button" style="margin-top: 10px;">
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
                        <a class="btn btn-default" href="<?php echo $this->createUrl("admin/labels/sa/editlabelset/lid/".$lid);?>" role="button" style="margin-top:10px;">
                            <span class="fa fa-pencil  text-success"></span>
                            <?php eT("Edit label set"); ?>
                        </a>
                        <?php endif; ?>


                    <!-- Export this label set -->
                    <?php if (Permission::model()->hasGlobalPermission('labelsets','export')):?>
                        <a class="btn btn-default" href="<?php echo $this->createUrl("admin/export/sa/dumplabel/lid/$lid");?>"
                            role="button"
                            style="margin-top:10px;">
                            <span class="icon-export text-success"></span>
                            <?php eT("Export this label set"); ?>
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>


                <!-- Delete label set -->
                <?php if (isset($labelbar['buttons']['delete']) && $labelbar['buttons']['delete'] == true ): ?>
                    <?php if (Permission::model()->hasGlobalPermission('labelsets','delete')): ?>
                        <button class="btn btn-default"
                           data-toggle="modal"
                           data-target="#confirmation-modal"
                           data-onclick='(function() { <?php echo convertGETtoPOST(Yii::app()->createUrl("admin/labels/sa/delete/", ["lid" => $lid])); ?> })'
                           data-message="<?php eT("Do you really want to delete this label set?","js"); ?>"
                           style="margin-top: 10px;">
                            <span class="fa fa-trash text-danger"></span>
                            <?php eT("Delete label set"); ?>
                        </button>
                        <?php endif; ?>
                    <?php endif; ?>

                <?php endif;?>

        </div>

        <!-- Right action buttons -->
        <div class="col-lg-6 text-right">

            <!-- view action buttons-->
            <?php if (isset($labelbar['buttons']['view'])):?>

                <!-- return to admin panel -->
                <a class="btn btn-default pull-right" href="<?php echo $this->createUrl('admin/index'); ?>" role="button" style="display: block; margin-top: 10px;">
                    <span class="fa fa-backward"></span>
                    &nbsp;&nbsp;
                    <?php eT('Back'); ?>
                </a>

                <?php endif; ?>

            <!-- edition action buttons -->
            <?php if (isset($labelbar['buttons']['edition'])):?>
                <a class="btn btn-success" href="#" role="button" id="save-form-button" data-form-id="<?php echo $labelbar['savebutton']['form']; ?>" style="margin: 10px 0 10px 0;">
                    <span class="fa fa-floppy-o"></span>
                    <?php echo $labelbar['savebutton']['text'];?>
                </a>
                <?php endif;?>

            <!-- Close -->
            <?php if(isset($labelbar['closebutton']['url'])):?>
                <a class="btn btn-danger" href="<?php echo $labelbar['closebutton']['url']; ?>" role="button" style="margin: 10px 0 10px 0;">
                    <span class="fa fa-close"></span>
                    <?php eT("Close");?>
                </a>
                <?php endif;?>
        </div>
    </div>
</div>
