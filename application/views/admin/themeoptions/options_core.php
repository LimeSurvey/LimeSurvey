    <?php foreach($aOptionAttributes['categories'] as $key => $category){ ?>
        <div role="tabpanel" class="tab-pane  <?php echo $key == 0 ? 'active' : ''; ?>" id="category-<?php echo $key; ?>">
            <div class="container-fluid" style="position:relative">
                <?php if ($key == 0){ ?>
                    <?php /* Small loading animation to give the scripts time to parse and render the correct values */ ?>
                    <div class="" style="display:none;height:100%;width:100%;position:absolute;left:0;top:0;background:rgb(255,255,255);background:rgba(235,235,235,0.8);z-index:2000;">
                        <div style="position:absolute; left:49%;top:35%;" class="text-center">
                            <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
                        </div>
                    </div>
                    <?php /* If this is a surveyspecific settings page, offer the possibility to do a full inheritance of the parent template */
                    if (!empty($aTemplateConfiguration['sid']) || !empty($aTemplateConfiguration['gsid'])){ ?>
                    <div class='row' id="general_inherit_active">
                        <div class='form-group row'>
                            <label for='simple_edit_options_general_inherit' class='control-label'><?php echo gT("Inherit everything" ); ?></label>
                            <div class='col-sm-12'>
                                <div class="btn-group" data-toggle="buttons">
                                    <label class="btn btn-default">
                                        <input id="general_inherit_on" name='general_inherit' type='radio' value='on' class='selector_option_general_inherit ' data-id='simple_edit_options_general_inherit'/>
                                        <?php echo gT("Yes"); ?>
                                    </label>
                                    <label class="btn btn-default">
                                        <input id="general_inherit_off" name='general_inherit' type='radio' value='off' class='selector_option_general_inherit ' data-id='simple_edit_options_general_inherit'/>
                                        <?php echo gT("No"); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php } ?>                 
                    
                    <?php } ?>
                    <div class='row action_hide_on_inherit'>
                    <?php
                        // options
                        foreach($aOptionAttributes['optionAttributes'] as $optionKey => $option){
                            if ($category == $option['category']){
                                echo $optionKey.'-->';
                            }
                        }

                    ?>
                    </div>

                </div>
                
            

        </div>

    <?php } ?>
