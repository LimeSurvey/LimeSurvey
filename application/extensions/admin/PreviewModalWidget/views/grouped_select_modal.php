<?php
/**
 * View for a selector modal with preview capabilities and a grouped structure
 */
?>

<?php //The modal ?>

<div class="modal fade previewModalWidget" tabindex="-1" role="dialog" id="selector__<?=$this->widgetsJsName?>-modal">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?=gT($this->modalTitle)?></h4>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
          <div class="row">
            <div class="col-xs-4 ls-ba">
              <div class="panel-group" id="accordion_<?=$this->widgetsJsName?>" role="tablist" aria-multiselectable="true">
                <?php foreach ($this->groupStructureArray as $sGroupTitle => $aGroupArray) { ?>
                  <div class="panel panel-default">
                    <div class="panel-heading" role="tab" id="heading_<?=$sGroupTitle?>">
                      <h4 class="panel-title">
                        <a role="button" class="collapsed" data-toggle="collapse" data-parent="#accordion_<?=$this->widgetsJsName?>" href="#collapsible_<?=$sGroupTitle?>" >
                            <?=$aGroupArray[$this->groupTitleKey]?>
                        </a>
                      </h4>
                    </div>
                    <div id="collapsible_<?=$sGroupTitle?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="<?=$sGroupTitle?>">
                      <div class="panel-body ls-space padding all-0">
                        <div class="list-group ls-space margin all-0">
                          <?php foreach ($aGroupArray[$this->groupItemsKey] as $sItemKey => $aItemContent) { ?>
                            <a 
                              href="#" 
                              class="list-group-item selector__Item--select-<?=$this->widgetsJsName?>" 
                              id="selector__<?=$this->widgetsJsName?>-Item--<?=$aItemContent['class']?>" 
                              data-item-value='<?=json_encode([
                                    "key" => $sItemKey,
                                    "title" => htmlentities($aItemContent['description']),
                                    "itemArray" => $aItemContent
                                ]); ?>' 
                            >
                              <?=$aItemContent['description']?>
                                <?php if (YII_DEBUG) {
                                    ?>
                                  <em class="small"><?=gT($this->debugKeyCheck)?> <?=$sItemKey?></em>
                                <?php
                                } ?>
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
                            <b><?=gT($this->previewWindowTitle)?></b><br/>
                            <p id="selector__<?=$this->widgetsJsName?>-currentSelected"><?=$this->currentSelected?></p>
                            </h3>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 currentImageContainer" id="selector__<?=$this->widgetsJsName?>-selectedImage">
                            <img src="<?=Yii::app()->getConfig('imageurl')?>/screenshots/<?=$this->value?>.png" />
                        </div>
                    </div>
                </div>
            </div>
          </div>
          <input id="<?=$this->widgetsJsName?>" name="<?=$this->widgetsJsName?>" value="<?=$this->value?>" type="hidden" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
          <?=gT($this->closeButton)?>
        </button>
        <button type="button" id="selector__select-this-<?=$this->widgetsJsName?>" class="btn btn-primary">
          <?=gT($this->selectButton)?>
        </button>
      </div>
    </div>
  </div>
</div>