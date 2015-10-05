<script type="text/javascript">
    var sImageURL = '<?php echo $sImageURL ?>';
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
                    <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/add.png" />
                    <?php eT("Create or import new label set(s)"); ?>
                </a>
                
    
                <!-- Export Multiple -->
                <?php if (Permission::model()->hasGlobalPermission('labelsets','export')):?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/labels/sa/exportmulti");?>" role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/dumplabelmulti.png" />
                        <?php eT("Export multiple label sets"); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>  

            <!-- Edition buttons -->                                  
            <?php if (isset($labelbar['buttons']['edit'])):?>
                
                <!-- Edit label set -->
                <?php if (Permission::model()->hasGlobalPermission('labelsets','update')):?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/labels/sa/editlabelset/lid/".$lid);?>" role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/edit.png" />
                        <?php eT("Edit label set"); ?>
                    </a>
                <?php endif; ?>

                <!-- Delete label set -->
                <?php if (Permission::model()->hasGlobalPermission('labelsets','update')):?>
                    <a class="btn btn-default" href='#' data-action='deletelabelset' data-url='<?php echo $this->createUrl("admin/labels/sa/process"); ?>' data-confirm='<?php eT('Do you really want to delete this label set?'); ?>' role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/delete.png" />
                        <?php eT("Delete label set"); ?>
                    </a>
                <?php endif; ?>

                <!-- Export this label set -->    
                <?php if (Permission::model()->hasGlobalPermission('labelsets','export')):?>
                    <a class="btn btn-default" href="<?php echo $this->createUrl("admin/export/sa/dumplabel/lid/$lid");?>" role="button">
                        <img src="<?php echo Yii::app()->getBaseUrl(true);?>/images/lime-icons/328637/dumplabel.png" />
                        <?php eT("Export this label set"); ?>
                    </a>
                <?php endif; ?>
            <?php endif; ?>    
            
             <?php if (isset($labelbar['buttons']['edition'])):?>

                <?php if (Permission::model()->hasGlobalPermission('labelsets','delete')): ?>
                    <a class="btn btn-default" role="button" data-action='deletelabelset' data-url='<?php echo $this->createUrl("admin/labels/sa/process"); ?>' data-confirm='<?php eT('Do you really want to delete this label set?'); ?>' >
                        <img src="<?php echo $sImageURL; ?>delete.png" />
                        <?php eT("Delete label set"); ?>
                    </a>
                <?php endif; ?>
                 
             <?php endif;?>
                      
        </div>
        
        
        <!-- Right action buttons --> 
        <div class="col-lg-6 text-right">
            
            <!-- view action buttons-->
            <?php if (isset($labelbar['buttons']['view'])):?>
                
                <!-- return to admin pannel -->
                <a class="btn btn-default pull-right" href="<?php echo $this->createUrl('admin/index'); ?>" role="button" style="display: block">
                    <span class="glyphicon glyphicon-backward" aria-hidden="true"></span>
                    &nbsp;&nbsp;
                    <?php eT('return to admin pannel'); ?>
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
                <a class="btn btn-success" href="#" role="button" id="save-form-button" aria-data-form-id="<?php echo $labelbar['savebutton']['form']; ?>">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    
                    <?php echo $labelbar['savebutton']['text'];?>
                </a>                
                <a class="btn btn-danger" href="<?php echo $this->createUrl($labelbar['closebutton']['url']); ?>" role="button">
                    <span class="glyphicon glyphicon-close" aria-hidden="true"></span>
                    <?php eT("Close");?>
                </a>
            <?php endif;?>          
        </div>
    </div>
</div>
