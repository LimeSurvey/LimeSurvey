<ul class="dropdown-menu">

    <!-- Import responses from an archived reponse table -->
    <li>
        <a class="dropdown-item"
           href='<?php echo Yii::App()->createUrl("admin/dataentry/sa/import/surveyid/$oSurvey->sid"); ?>'
           role="button">
            <?php eT("Import responses from an archived reponse table"); ?>
        </a>
    </li>

    <!-- Import a VV survey file -->
    <li>
        <a class="dropdown-item"
           href='<?php echo Yii::App()->createUrl("admin/dataentry/sa/vvimport/surveyid/$oSurvey->sid"); ?>'
           role="button">
            <?php eT("Import a VV survey file"); ?>
        </a>
    </li>
</ul>
