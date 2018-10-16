<?php die();?>
<div id='questionbottom'>
    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
        <div class="panel panel-default" id="questionTypeContainer">
            <div class="panel-heading" role="tab" id="headingOne">
              <div class="panel-title h4">
                  <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion">
                      <span class="fa fa-chevron-left"></span>
                  </a>
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                 <?php eT("General options");?>
                </a>
              </div>
            </div>
            <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                <div class="panel-body">
                    <div>
                        <div class="form-group">
                            <label for='question_type_button'>
                                <?php eT("Question Type:"); ?>
                            </label>
                            <div class="">
                            <?php if($selectormodeclass!="none"): ?>
                                <?php
                                    foreach (getQuestionTypeList($eqrow['type'], 'array') as $key=> $questionType)
                                    {
                                        if (!isset($groups[$questionType['group']]))
                                        {
                                            $groups[$questionType['group']] = array();
                                        }
                                        $groups[$questionType['group']][$key] = $questionType['description'];
                                    }
                                ?>

                                <select class="form-control" id="question_type" style="display: none;">
                                <?php foreach($groups as $name => $group):?>
                                    <optgroup label="<?php echo $name;?>">
                                        <?php foreach($group as $type => $option):?>
                                            <option class="questionTypeOld" value="<?php echo $type;?>" <?php if($type == $eqrow['type']){echo 'selected';}?> ><?php echo $option;?></option>
                                        <?php endforeach;?>
                                    </optgroup>
                                <?php endforeach;?>
                                </select>

                                <div class="btn-group" id="question_type_button" style="z-index: 1000">
                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="z-index: 1000">
                                        <?php foreach($groups as $name => $group):?>
                                            <?php foreach($group as $type => $option):?>
                                                <?php if($type == $eqrow['type']){echo $option;}?>
                                            <?php endforeach;?>
                                        <?php endforeach;?>
                                        <span class="caret"></span>
                                    </button>

                                    <ul class="dropdown-menu" style="z-index: 1000">

                                        <?php foreach($groups as $name => $group):?>
                                            <small><?php echo $name;?></small>

                                        <?php foreach($group as $type => $option):?>
                                                <li>
                                                    <a href="#" class="questionType" <?php if($type == $eqrow['type']){echo 'active';}?>><?php echo $option;?></a>
                                                </li>
                                            <?php endforeach;?>

                                            <li role="separator" class="divider"></li>
                                        <?php endforeach;?>

                                    </ul>
                                </div>
                            <?php else: ?>
                                <?php
                                    $aQtypeData=array();
                                    foreach (getQuestionTypeList($eqrow['type'], 'array') as $key=> $questionType)
                                    {
                                        $aQtypeData[]=array('code'=>$key,'description'=>$questionType['description'],'group'=>$questionType['group']);
                                    }
                                    echo CHtml::dropDownList(
                                        'type',
                                        'category',
                                        CHtml::listData($aQtypeData,'code','description','group'),
                                        array(
                                            'class' => 'none',
                                            'id'=>'question_type',
                                            'options' => array($eqrow['type']=>array('selected'=>true))
                                        )
                                    );
                                ?>
                            <?php endif; ?>
                            </div>
                        </div>

                        <div  class="form-group">
                            <label for='gid'><?php eT("Question group:"); ?></label>
                            <select name='gid' id='gid' class="form-control">
                                <?php echo getGroupList3($eqrow['gid'],$surveyid); ?>
                            </select>
                        </div>

                        <div  class="form-group" id="OtherSelection">
                            <label><?php eT("Option 'Other':"); ?></label>
                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array('name' => 'optionother', 'onLabel'=>gT('On'),'offLabel'=>gT('Off')));?>
                        </div>

                        <div id='MandatorySelection'  class="form-group">
                            <label><?php eT("Mandatory:"); ?></label>
                            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array('name' => 'switchbuttontest', 'onLabel'=>gT('On'),'offLabel'=>gT('Off')));?>
                        </div>

                        <div  class="form-group">
                            <label for='relevance'><?php eT("Relevance equation:"); ?></label>
                            <textarea cols='1' class="form-control" rows='1' id='relevance' name='relevance' ></textarea>
                        </div>

                        <div id='Validation'  class="form-group">
                            <label for='preg'><?php eT("Validation:"); ?></label>
                            <input type='text' id='preg' name='preg' size='50' value="<?php echo $eqrow['preg']; ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingTwo">
                <div class="panel-title h4">
                    <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion">
                        <span class="fa fa-chevron-left"></span>
                    </a>
                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        <?php eT("Advanced settings"); ?>
                    </a>
                </div>
            </div>
            <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
                <div class="panel-body">
                    <p>
                        <a id="showadvancedattributes"><?php eT("Show advanced settings"); ?></a><a id="hideadvancedattributes" style="display:none;"><?php eT("Hide advanced settings"); ?></a>
                    </p>

                    <div id="advancedquestionsettingswrapper" style="display:none;">
                        <div class="loader">
                            <?php eT("Loading..."); ?>
                        </div>

                        <div id="advancedquestionsettings">
                            <!-- Content append via ajax -->
                        </div>
                    </div>

                    <br />
                <br/>
                </div>
            </div>
        </div>
    </div>
</div>
