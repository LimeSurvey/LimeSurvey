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
                    <div class="item text-center <?php if($key==$surveyinfo['template']){echo ' active ';}else{echo ' inactive ';}?>" id="template-big-<?php echo $key;?>">
                        <img class="img-responsive imgSelectTemplate" src="<?php echo Yii::app()->request->baseUrl.'/templates/'.$key.'/preview.png'; ?>" alt="<?php echo $key;?>">

                            <?php if($key==$surveyinfo['template']):?>
                                <a href="#" class="selectTemplate btn btn-default btn-success btn-xs disabled" data-unselectedtext="<?php eT('Select');?> &nbsp <?php echo $key;?>"><?php eT('Selected!');?></a>
                            <?php else:?>
                                <button data-selectedtext="<?php eT("Selected!");?>" data-url="<?php echo Yii::app()->urlManager->createUrl("admin/survey/sa/changetemplate/surveyid/$iSurveyId/template/$key" ); ?>" data-template="<?php echo $key;?>" class="selectTemplate btn btn-default btn-xs">
                                    <?php eT('Select');?>&nbsp<?php echo $key;?>
                                </button>
                            <?php endif;?>

                    </div>
                <?php endforeach;?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12" id="jcarousel-wrapper-container"> <!-- width defined in css -->
                <div class="jcarousel-wrapper" style="z-index: 2000">
                    <div class="jcarousel">
                        <ul >
                            <?php foreach($templates as $key=>$template):?>
                                <li class="template-miniature <?php if($key==$surveyinfo['template']){echo ' active';}?>" data-big="#template-big-<?php echo $key;?>">
                                    <img src="<?php echo Yii::app()->request->baseUrl.'/templates/'.$key.'/preview.png'; ?>" alt="<?php echo $key;?>"  >
                                </li>
                            <?php endforeach;?>
                        </ul>
                    </div>

                    <a href="#" class="jcarousel-control-prev">&lsaquo;</a>
                    <a href="#" class="jcarousel-control-next">&rsaquo;</a>
                </div>

            </div>
        </div>





    </div>
    <?php /*
    <div id="carousel-example-generic" class="carousel slide col-lg-5 col-md-12 col-sm-12" data-ride="carousel" data-interval="false">
        <!-- Indicators -->
        <div class="carousel-indicators" id="template-miniature" >
            <?php foreach($templates as $key=>$template):?>
                <a href="#" data-target="#carousel-example-generic" data-slide-to="<?php echo $count;?>" <?php if($key==$surveyinfo['template']){echo 'class="active"';}?> data-toggle="tooltip" data-placement="bottom" title="<?php echo $key;?>" >
                    <img src="<?php echo Yii::app()->request->baseUrl.'/templates/'.$key.'/preview.png'; ?>" alt="<?php echo $key;?>"  width="42">
                </a>
                <?php $count++;?>
            <?php endforeach; ?>
        </div>
        <h4 class="panel-title">Select your template</h4>
        <div class="carousel-inner" role="listbox">
            <?php foreach($templates as $key=>$template):?>
                <div class="item <?php if($key==$surveyinfo['template']){echo ' active ';}?>">
                    <img src="<?php echo Yii::app()->request->baseUrl.'/templates/'.$key.'/preview.png'; ?>" alt="<?php echo $key;?>">

                    <div class="carousel-caption">

                        <?php if($key==$surveyinfo['template']):?>
                            <a href="#" class="selectTemplate btn btn-default btn-success btn-xs disabled"><?php eT('Selected!');?></a>
                        <?php else:?>
                            <button data-selectedtext="<?php eT("Selected!");?>" data-url="<?php echo Yii::app()->urlManager->createUrl("admin/survey/sa/changetemplate/surveyid/$iSurveyId/template/$key" ); ?>" data-template="<?php echo $key;?>" class="selectTemplate btn btn-default btn-xs"><?php eT('Select');?> <?php echo $key;?></button>
                        <?php endif;?>
                    </div>
                </div>
            <?php endforeach;?>
        </div>

        <!-- Controls -->
          <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
            <span class="glyphicon glyphicon-chevron-left" ></span>
            <span class="sr-only">Previous</span>
          </a>
          <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
            <span class="glyphicon glyphicon-chevron-right"></span>
            <span class="sr-only">Next</span>
          </a>
      </div>
      */ ?>
  </div>
