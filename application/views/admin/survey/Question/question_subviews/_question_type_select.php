  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?=gT("Select Question type")?></h4>
  </div>
  <div class="modal-body">
    <div class="container-fluid">
      <div class="row">
        <div class="col-xs-4 ls-ba">
          <div class="panel-group" id="accordion_questionGroups" role="tablist" aria-multiselectable="true">
            <?php foreach ($aQuestionTypeGroups as $sGroupHTMLConformString => $aQuestionTypeGroup) { ?>
              <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="heading_<?=$sGroupHTMLConformString?>">
                  <h4 class="panel-title">
                    <a role="button" class="collapsed" data-toggle="collapse" data-parent="#accordion_questionGroups" href="#collapsible_<?=$sGroupHTMLConformString?>" >
                        <?=$aQuestionTypeGroup['questionGroupName']?>
                    </a>
                  </h4>
                </div>
                <div id="collapsible_<?=$sGroupHTMLConformString?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?=$sGroupHTMLConformString?>">
                  <div class="panel-body ls-space padding all-0">
                    <div class="list-group ls-space margin all-0">
                      <?php foreach ($aQuestionTypeGroup['questionTypes'] as $sQuestionTypeKey => $aQuestionType) { ?>
                        <a href="#" class="list-group-item selector__select-question-type" id="selector__question-type-select-modal_question-type-<?=$aQuestionType['class']?>" data-question-type='<?php
                            echo json_encode([
                                "key" => $sQuestionTypeKey,
                                "title" => $aQuestionType['description']
                            ]);
                        ?>' >
                          <?=$aQuestionType['description']?>
                            <?php if(YII_DEBUG) {?>
                              <em class="small">Type code: <?=$sQuestionTypeKey?></em>
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
                        <b><?=gT("Selected: ")?></b><br/>
                        <p id="selector__currentQuestionTypeTitle"><?=Question::getQuestionTypeName($currentType) ?></p>
                        </h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12" id="selector__currentQuestionTypeImage">
                        <img src="<?=Yii::app()->getConfig('imageurl')?>/screenshots/<?=$currentType?>.png" />
                    </div>
                </div>
            </div>
        </div>
      </div>
      <input id="selector__selected_questiontype" value="<?=$currentType?>" type="hidden" />
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">
      <?=gt("Close")?>
    </button>
    <button type="button" id="selector__select-this-questiontype" class="btn btn-primary">
      <?=gt("Select this")?>
    </button>
  </div>
