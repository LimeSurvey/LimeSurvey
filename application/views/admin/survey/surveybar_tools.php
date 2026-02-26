<?php

/**
 * Subview of surveybar_view.
 * @param $surveydelete
 * @param $surveytranslate
 * @param $hasadditionallanguages
 * @param $oSurvey
 * @param $conditionscount
 * @param $onelanguage
 * @param $surveycontentread
 * @param $extraToolsMenuItems
 */

?>

<div class="btn-group d-none d-md-block">

    <!-- Main button dropdown -->
    <button id="ls-tools-button" type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="ri-tools-fill" ></span>
         <?php eT('Tools'); ?><span class="caret"></span>
    </button>

    <!-- dropdown -->
    <ul class="dropdown-menu">
        <?php if ($surveydelete): ?>

              <!-- Delete survey -->
              <li>
                  <a href="<?php echo $this->createUrl("surveyAdministration/delete/surveyid/{$oSurvey->sid}"); ?>">
                    <span class="ri-delete-bin-fill text-danger" ></span>
                    <?php eT("Delete survey"); ?>
                  </a>
              </li>
        <?php endif; ?>

        <?php if ($surveytranslate): ?>
              <!-- surveytranslate -->

            <?php if ($hasadditionallanguages): ?>

                    <!-- Quick-translation -->
                    <li>
                        <a href="<?php echo $this->createUrl("quicktranslation/index/surveyid/{$oSurvey->sid}"); ?>">
                        <span class="ri-global-line" ></span>
                        <?php eT("Quick-translation"); ?>
                        </a>
                    </li>

            <?php else: ?>

                    <!-- Quick-translation disabled -->
                    <li>
                        <a href="#" onclick="alert('<?php eT("Currently there are no additional languages configured for this survey.", "js"); ?>');" >
                          <span class="ri-global-line" ></span>
                          <?php eT("Quick-translation"); ?>
                        </a>
                    </li>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'update')): ?>
              <li>
                <?php if ($conditionscount > 0):?>

                      <!-- condition -->
                      <a href="<?php echo $this->createUrl("/admin/conditions/sa/index/subaction/resetsurveylogic/surveyid/{$oSurvey->sid}"); ?>">
                        <span class="ri-survey-fill" ></span>
                        <?php eT("Reset conditions"); ?>
                      </a>
                <?php else : ?>

                      <!-- condition disabled -->
                      <a href="#" onclick="alert('<?php eT("Currently there are no conditions configured for this survey.", "js");
?>');" >
                        <span class="ri-survey-fill" ></span>
                        <?php eT("Reset conditions"); ?>
                      </a>
                <?php endif; ?>
              </li>
          <?php endif; ?>

          <?php if (!empty($extraToolsMenuItems)): ?>
              <?php foreach ($extraToolsMenuItems as $menuItem): ?>
                  <?php if ($menuItem->isDivider()): ?>
                      <li class="dropdown-divider"></li>
                <?php elseif ($menuItem->isSmallText()): ?>
                      <li class="dropdown-header"><?php echo $menuItem->getLabel(); ?></li>
                <?php else: ?>
                      <li>
                          <a href="<?php echo $menuItem->getHref(); ?>">
                              <!-- Spit out icon if present -->
                              <?php if ($menuItem->getIconClass() != ''): ?>
                                <span class="<?php echo $menuItem->getIconClass(); ?>">&nbsp;</span>
                              <?php endif; ?>
                              <?php echo $menuItem->getLabel(); ?>
                          </a>
                      </li>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if ($surveycontentread): ?>
            <!-- survey content -->

            <?php if ($onelanguage): ?>
                <!-- one language -->

                <!-- Survey logic overview -->
                <li>
                    <a href='<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/$oSurvey->sid/"); ?>' >
                        <span class="ri-checkbox-fill" ></span>
                        <?php eT("Survey logic overview"); ?>
                    </a>
                </li>
            <?php else : ?>
                <!-- multilangue  -->

                <li role="separator" class="dropdown-divider"></li>

                <!-- Survey logic overview -->
                <li class="dropdown-header"><?php eT("Survey logic overview"); ?></li>
                <?php foreach ($oSurvey->allLanguages as $tmp_lang): ?>
                    <!-- Languages -->

                    <li>
                        <a  href='<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/$oSurvey->sid/lang/$tmp_lang"); ?>'>
                               <span class="ri-checkbox-fill" ></span>
                               <?php echo getLanguageNameFromCode($tmp_lang, false); ?>
                           </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!$oSurvey->isActive && Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'update')): ?>
            <li role="separator" class="dropdown-divider"></li>

            <!-- Regenerate question codes -->
            <li class="dropdown-header">
                <?php eT("Regenerate question codes"); ?>
            </li>

            <!-- Straight -->
            <li>
                <a href="<?php
                echo $this->createUrl("/surveyAdministration/regenerateQuestionCodes/surveyid/{$oSurvey->sid}/subaction/straight");
                ?>">
                <span class="ri-survey-fill" ></span>
                <?php eT("Straight"); ?>
                </a>
            </li>

            <!-- By question group -->
            <li>
            <a href="<?php echo $this->createUrl("surveyAdministration/regenerateQuestionCodes/surveyid/{$oSurvey->sid}/subaction/bygroup"); ?>">
                <span class="ri-survey-fill" ></span>
                <?php eT("By question group"); ?>
            </a>
            </li>
            <?php endif; ?>
        </ul>
</div>
