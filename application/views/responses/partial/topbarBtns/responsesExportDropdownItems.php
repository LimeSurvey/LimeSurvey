<ul class="dropdown-menu">

    <!-- Export results to application -->
    <li>
        <a class="dropdown-item"
           href='<?php echo Yii::App()->createUrl("admin/export/sa/exportresults/surveyid/$oSurvey->sid"); ?>'>
            <?php eT("Export responses"); ?>
        </a>
    </li>

    <!-- Export results to a SPSS/PASW command file -->
    <li>
        <a class="dropdown-item"
           href='<?php echo Yii::App()->createUrl("admin/export/sa/exportspss/sid/$oSurvey->sid"); ?>'>
            <?php eT("Export responses to SPSS"); ?>
        </a>
    </li>

    <!-- Export a VV survey file -->
    <li>
        <a class="dropdown-item"
           href='<?php echo Yii::App()->createUrl("admin/export/sa/vvexport/surveyid/$oSurvey->sid"); ?>'>
            <?php eT("Export a VV survey file"); ?>
        </a>
    </li>

</ul>
