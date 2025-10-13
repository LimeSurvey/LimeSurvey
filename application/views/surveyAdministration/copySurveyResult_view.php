<?php
/**
 * Render the result of the import survey action
 */

use LimeSurvey\Models\Services\CopySurveyResult;

/** @var $copyResults CopySurveyResult  */
/** @var $sLink string */

?>



<!-- Import Failed -->
<?php if ($copyResults->getErrors()) {?>

    <div class="jumbotron message-box message-box-error">
        <h2 class="danger"><?php eT("Copy survey");?></h2>
        <p class="lead text-danger">
            <?php eT("Error"); ?>
        </p>
        <!-- error message -->
        <p>
        <?php
        foreach ($copyResults->getErrors() as $sErrorMessage) {
            echo $sErrorMessage;
        } ?>
        </p>
        <!-- buttons -->
        <p>
            <input type='submit' class="btn btn-outline-secondary btn-large" value='<?php eT("Main Admin Screen"); ?>' onclick="window.open('<?php echo $this->createUrl('admin/'); ?>', '_top')" />
        </p>
    </div>

<!-- Success -->
<?php } else {?>
    <div class="jumbotron message-box">
        <h2 class="text-success"><?php eT("Success"); ?></h2>

        <p class="lead"><?php eT("Survey copy summary"); ?></p>

        <!-- Import result messages -->
        <div class="row justify-content-center">
            <div class="col-lg-2">
                <table class="table table-striped table-condensed ">
                    <tr>
                        <td><?php eT("Surveys"); ?>:</td>
                        <td><?php echo $copyResults->getCntSurveys(); ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Languages"); ?>:</td>
                        <td><?php echo $copyResults->getCntSurveyLanguages(); ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Question groups"); ?>:</td>
                        <td><?php echo $copyResults->getCntQuestionGroups(); ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Questions"); ?>:</td>
                        <td><?php echo $copyResults->getCntQuestions(); ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Assessments"); ?>:</td>
                        <td><?php echo $copyResults->getCntAssessments(); ?></td>
                    </tr>
                    <tr>
                        <td><?php eT("Quotas"); ?>:</td>
                        <td><?php echo $copyResults->getCntQuotas(); ?></td>
                    </tr>
                </table>
            </div>
        </div>


        <!-- Result -->
        <p class="text-info"><?php eT("Copy of survey is completed.")?></p>

        <!-- Buttons -->
        <p>
            <input type='submit'
                   class="btn btn-outline-secondary btn-large"
                   value='<?php eT("Go to survey");?>'
                   onclick="window.open('<?php echo $sLink; ?>', '_top')"><br /><br />
        </p>
    </div>
<?php }?>
