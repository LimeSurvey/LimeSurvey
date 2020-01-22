  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?php eT("Select question type")?></h4>
  </div>
  <div class="modal-body">
    <div class="container-fluid">
      <div class="row">
        <div class="col-xs-4 ls-ba">
          <div class="panel-group" id="accordion_questionGroups" role="tablist" aria-multiselectable="true">
            <?php foreach ($aQuestionTypeGroups as $sGroupHTMLConformString => $aQuestionTypeGroup) { ?>
              <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="heading_<?php echo $sGroupHTMLConformString?>">
                  <h4 class="panel-title">
                    <a role="button" class="collapsed" data-toggle="collapse" data-parent="#accordion_questionGroups" href="#collapsible_<?php echo $sGroupHTMLConformString?>" >
                        <?php echo $aQuestionTypeGroup['questionGroupName']?>
                    </a>
                  </h4>
                </div>
                <div id="collapsible_<?php echo $sGroupHTMLConformString?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?php echo $sGroupHTMLConformString?>">
                  <div class="panel-body ls-space padding all-0">
                    <div class="list-group ls-space margin all-0">
                      <?php foreach ($aQuestionTypeGroup['questionTypes'] as $sQuestionTypeKey => $aQuestionType) { ?>
                        <a href="#" class="list-group-item selector__select-question-type" id="selector__question-type-select-modal_question-type-<?php echo $aQuestionType['class']?>" data-question-type='<?php
                            echo json_encode([
                                "key" => $sQuestionTypeKey,
                                "title" => htmlentities($aQuestionType['description'])
                            ]);
                        ?>' >
                          <?php echo $aQuestionType['description']?>
                            <?php if(YII_DEBUG) {?>
                              <em class="small">Type code: <?php echo $sQuestionTypeKey?></em>
                              <?php  }?>
                        </a>
                        <?php } ?>
                    </div>
                  </div>
                </div>
              </div>
              <?php } ?>
            </div>
        </div>
        <div class="col-xs-8">
            <div class="container-center">
                <div class="row">
                    <div class="col-sm-12">
                        <h3> 
                        <b><?php eT("Selected: ")?></b><br/>
                        <p id="selector__currentQuestionTypeTitle"><?php echo Question::getQuestionTypeName($currentType) ?></p>
                        </h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 currentQuestionTypeImageContainer" id="selector__currentQuestionTypeImage">
                        <img src="<?php echo Yii::app()->getConfig('imageurl')?>/screenshots/<?php echo $currentType?>.png" />
                    </div>
                </div>
            </div>
        </div>
      </div>
      <input id="selector__selected_questiontype" value="<?php echo $currentType?>" type="hidden" />
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
      <?php eT("Close")?>
    </button>
    <button type="button" id="selector__select-this-questiontype" class="btn btn-primary">
      <?php eT("Select this")?>
    </button>
  </div>
