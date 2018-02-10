<?php

/**
 * Subview of surveybar_view.
 * @param $respstatsread
 * @param $surveyexport
 * @param $oSurvey
 * @param $onelanguage
 */

?>

<div class="btn-group hidden-xs">

    <!-- Main dropdown -->
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <span class="icon-display_export" ></span>
      <?php eT("Display / Export"); ?> <span class="caret"></span>
    </button>

    <!-- dropdown -->
    <ul class="dropdown-menu">

        <!-- Export -->
        <li class="dropdown-header"> <?php eT("Export..."); ?></li>

        <!-- Survey structure -->
        <li>
            <a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportstructurexml/surveyid/$oSurvey->sid"); ?>' >
                <span class="icon-export" ></span>
                <?php eT("Survey structure (.lss)"); ?>
            </a>
        </li>

        <?php if ($respstatsread && $surveyexport): ?>
            <?php if ($oSurvey->isActive):?>

                <!-- Survey archive -->
                <li>
                    <a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportarchive/surveyid/$oSurvey->sid"); ?>' >
                        <span class="icon-export" ></span>
                        <?php eT("Survey archive (.lsa)"); ?>
                    </a>
                </li>
            <?php else: ?>
                <!-- Survey archive unactivated -->
                <li>
                    <a href="#" onclick="alert('<?php eT("You can only archive active surveys.", "js"); ?>');" >
                        <span class="icon-export" ></span>
                        <?php eT("Survey archive (.lsa)"); ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <!-- queXML -->
        <li>
          <a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportstructurequexml/surveyid/$oSurvey->sid"); ?>' >
              <span class="icon-export" ></span>
              <?php eT("queXML format (*.xml)"); ?>
          </a>
        </li>

        <!-- queXMLPDF -->
        <li>
          <a href='<?php echo $this->createUrl("admin/export/sa/quexml/surveyid/$oSurvey->sid"); ?>' >
              <span class="icon-export" ></span>
              <?php eT("queXML PDF export"); ?>
          </a>
        </li>


        <!-- Tab-separated-values -->
        <li>
          <a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportstructuretsv/surveyid/$oSurvey->sid"); ?>' >
              <span class="icon-export" ></span>
              <?php eT("Tab-separated-values format (*.txt)"); ?>
          </a>
        </li>

        <!-- Survey printable version  -->
        <li>
          <a href='<?php echo $this->createUrl("admin/export/sa/survey/action/exportprintables/surveyid/$oSurvey->sid"); ?>' >
              <span class="icon-export" ></span>
              <?php eT("Printable survey (*.html)"); ?>
          </a>
        </li>

        <?php if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'read')): ?>
            <?php if ($onelanguage):?>
                <!-- Printable version -->
                <li>
                    <a target='_blank' href='<?php echo $this->createUrl("admin/printablesurvey/sa/index/surveyid/$oSurvey->sid"); ?>' >
                        <span class="fa fa-print"></span>
                        <?php eT("Printable survey"); ?>
                    </a>
                </li>
            <?php else: ?>
                <li role="separator" class="divider"></li>
                <!-- Printable version multilangue -->
                <li class="dropdown-header"><?php eT("Printable version"); ?></li>
                <?php foreach ($oSurvey->allLanguages as $tmp_lang): ?>
                    <li>
                        <a accesskey='d' target='_blank' href='<?php echo $this->createUrl("admin/printablesurvey/sa/index/surveyid/$oSurvey->sid/lang/$tmp_lang"); ?>'>
                            <span class="fa fa-print"></span>
                            <?php echo getLanguageNameFromCode($tmp_lang, false); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
</div>
