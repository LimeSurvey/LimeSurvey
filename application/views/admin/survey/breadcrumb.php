<?php if(isset($oQuestion)): ?>
    <div class="row">
        <div class="col-sm-12">
            <ol class="breadcrumb">
                <li>
                    <a href="<?php echo App()->createUrl('/admin/survey/sa/view/surveyid/'. $oQuestion->sid );?>">
                        <?php echo $oQuestion->survey->defaultlanguage->surveyls_title;?>
                    </a>
                </li>

                <li>
                    <a href="<?php echo App()->createUrl('admin/survey/sa/listquestions/surveyid/'.$oQuestion->sid.'?group_name='.urlencode($oQuestion->groups->group_name).'&yt0=Search' );?>">
                        <?php echo $oQuestion->groups->group_name;?>
                    </a>
                </li>
                <?php if(!isset($active)): ?>
                    <li class="active">
                        <?php echo $oQuestion->title;?>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="<?php echo App()->createUrl('/admin/questions/sa/view/surveyid/'.$oQuestion->sid.'/gid/'.$oQuestion->gid.'/qid/'.$oQuestion->qid );?>">
                            <?php echo $oQuestion->title;?>
                        </a>
                    </li>
                    <li class="active">
                        <?php echo $active;?>
                    </li>
                <?php endif; ?>
            </ol>
        </div>
    </div>
<?php elseif(isset($oQuestionGroup)): ?>
    <div class="row">
        <div class="col-sm-12">
            <ol class="breadcrumb">
              <li>
                  <a href="<?php echo App()->createUrl('/admin/survey/sa/view/surveyid/'. $oQuestionGroup->sid );?>">
                      <?php echo $oQuestionGroup->survey->defaultlanguage->surveyls_title;?>
                  </a>
              </li>

              <?php if(!isset($active)): ?>
               <li class="active">
                      <?php echo $oQuestionGroup->group_name;?>
               </li>
              <?php else: ?>
                  <li>
                      <a href="<?php echo App()->createUrl('admin/questiongroups/sa/view/surveyid/'.$oQuestionGroup->sid.'/gid/'.$oQuestionGroup->gid  );?>">
                          <?php echo $oQuestionGroup->group_name;?>
                      </a>
                  </li>
                  <li class="active">
                      <?php echo $active;?>
                  </li>
              <?php endif; ?>
            </ol>
        </div>
    </div>
<?php endif;?>
