<div id="breadcrumb-container">
<?php if(isset($oQuestion)): ?>
        <div class="">
            <ol class="breadcrumb">
                <li>
                    <a href="<?php echo App()->createUrl('/admin/survey/sa/view/surveyid/'. $oQuestion->sid );?>">
                        <?php echo flattenText($oQuestion->survey->defaultlanguage->surveyls_title);;?>
                    </a>
                </li>

                <li>
                    <a href="<?php echo App()->createUrl('admin/survey/sa/listquestions/surveyid/'.$oQuestion->sid.'?group_name='.urlencode($oQuestion->groups->group_name).'&yt0=Search' );?>">
                        <?php echo flattenText($oQuestion->groups->group_name);?>
                    </a>
                </li>
                <?php if(!isset($active)): ?>
                    <li class="active">
                        <?php echo flattenText($oQuestion->title);?>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?php echo App()->createUrl('/admin/questions/sa/view/surveyid/'.$oQuestion->sid.'/gid/'.$oQuestion->gid.'/qid/'.$oQuestion->qid );?>">
                            <?php echo flattenText($oQuestion->title);?>
                        </a>
                    </li>
                    <li class="active">
                        <?php echo $active;?>
                    </li>
                <?php endif; ?>
            </ol>
        </div>
<?php elseif(isset($oQuestionGroup)): ?>
        <div class="">
            <ol class="breadcrumb">
              <li>
                  <a href="<?php echo App()->createUrl('/admin/survey/sa/view/surveyid/'. $oQuestionGroup->sid );?>">
                      <?php echo flattenText($oQuestionGroup->survey->defaultlanguage->surveyls_title);?>
                  </a>
              </li>

              <?php if(!isset($active)): ?>
               <li class="active">
                      <?php echo flattenText($oQuestionGroup->group_name);?>
               </li>
              <?php else: ?>
                  <li>
                      <a href="<?php echo App()->createUrl('admin/questiongroups/sa/view/surveyid/'.$oQuestionGroup->sid.'/gid/'.$oQuestionGroup->gid  );?>">
                          <?php echo flattenText($oQuestionGroup->group_name);?>
                      </a>
                  </li>
                  <li class="active">
                      <?php echo $active;?>
                  </li>
              <?php endif; ?>
            </ol>
        </div>
<?php elseif(isset($token)): ?>
        <div class="">
            <ol class="breadcrumb">
              <li>
                  <a href="<?php echo App()->createUrl('/admin/survey/sa/view/surveyid/'. $oSurvey->sid );?>">
                      <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title);?>
                  </a>
              </li>
              <li>
                  <a href="<?php echo App()->createUrl('admin/tokens/sa/index/surveyid/'. $oSurvey->sid );?>">
                      <?php eT('Survey participants');?>
                  </a>
              </li>
            <li class="active">
                <?php echo $active;?>
            </li>
            </ol>
        </div>
<?php elseif(isset($oSurvey)): ?>
        <div class="">
            <ol class="breadcrumb">
              <?php if(!isset($active)): ?>
                  <li>
                      <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title);?>
                  </li>
              <?php else: ?>
              <li>
                  <a href="<?php echo App()->createUrl('/admin/survey/sa/view/surveyid/'. $oSurvey->sid );?>">
                      <?php echo flattenText($oSurvey->defaultlanguage->surveyls_title);?>
                  </a>
              </li>
                  <li class="active">
                      <?php echo $active;?>
                  </li>
              <?php endif; ?>
            </ol>
        </div>
<?php endif;?>
</div>
