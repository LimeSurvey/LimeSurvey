<?php
/**
 * Display the template carousel
 *
 * @var $templates
 * @var $surveyinfo
 * @var $iSurveyId
 */
 $count = 0;
?>

<div class="row template-caroussel">
    <div class="col-sm-12" id='carrousel-container'>
        <div class="row">
            <div class="col-sm-12" id="item-container"> <!-- width defined in css -->
                <h4 class="panel-title"><?php eT('Select your template:'); ?></h4>

                <?php foreach($templates as $key=>$template):?>
                    <?php if (Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('templates','read') || hasTemplateManageRights(Yii::app()->session["loginID"], $key) == 1 || $surveyinfo['template']==htmlspecialchars($key) ): ?>
                    <div class="item text-center <?php if($key==$surveyinfo['template']){echo ' active ';}else{echo ' inactive ';}?>" id="template-big-<?php echo $key;?>">
                        <img class="img-responsive imgSelectTemplate" src="<?php echo $template['preview']; ?>" alt="<?php echo $key;?>">
                            <?php if($key==$surveyinfo['template']):?>
                                <button
                                    class="selectTemplate btn btn-default btn-success btn-xs disabled"
                                    data-selectedtext="<?php eT("Selected!");?>"
                                    data-unselectedtext="<?php eT('Select');?> &nbsp; <?php echo $key;?>">
                                        <?php eT('Selected!');?>
                                </button>
                            <?php else:?>
                                <button
                                    data-selectedtext="<?php eT("Selected!");?>"
                                    data-unselectedtext="<?php eT('Select');?> &nbsp; <?php echo $key;?>"
                                    data-url="<?php echo Yii::app()->urlManager->createUrl("admin/survey/sa/changetemplate/surveyid/$iSurveyId/template/$key" ); ?>"
                                    data-template="<?php echo $key;?>"
                                    class="selectTemplate btn btn-default btn-xs">
                                    <?php eT('Select');?>&nbsp;<?php echo $key;?>
                                </button>
                            <?php endif;?>
                    </div>
                <?php endif;?>
                <?php endforeach;?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12" id="jcarousel-wrapper-container"> <!-- width defined in css -->
                <div class="jcarousel-wrapper" >
                    <div class="jcarousel">
                        <ul >
                            <?php foreach($templates as $key=>$template):?>
                                <?php if (Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('templates','read') || hasTemplateManageRights(Yii::app()->session["loginID"], $key) == 1 || $surveyinfo['template']==htmlspecialchars($key) ): ?>
                                <li class="template-miniature <?php if($key==$surveyinfo['template']){echo ' active';}?>" data-big="#template-big-<?php echo $key;?>">
                                    <img src="<?php echo $template['preview']; ?>" alt="<?php echo $key;?>"  >
                                </li>
                            <?php endif; ?>
                            <?php endforeach;?>
                        </ul>
                    </div>

                    <?php if(count($templates)>4):?>
                        <a href="#" class="jcarousel-control-prev">&lsaquo;</a>
                        <a href="#" class="jcarousel-control-next">&rsaquo;</a>
                    <?php endif;?>
                </div>

            </div>
        </div>
    </div>
  </div>
