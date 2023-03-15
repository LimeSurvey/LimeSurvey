<?php

/**
 * Subview of surveybar_view.
 * @param $surveybar
 * @param $surveyid
 */

// @TODO unused file?
?>

<div class=" col-lg-4 text-end">
    <?php if (isset($surveybar['savebutton']['form'])):?>

        <!-- Save -->
        <button class="btn btn-primary" href="#" type="button" id="save-button">
            <span class="ri-check-fill"></span>
            <?php if (isset($surveybar['savebutton']['text']))
            {
                echo $surveybar['savebutton']['text'];
            }
            else {
                eT("Save");
            }?>
        </button>
        <?php if (isset($surveybar['importquestiongroup'])):?>
            <?php
                //Save and new button
                $paramArray = array();
                $paramArray["surveyid"] = $surveyid;
                $saveAndNewLink = $this->createUrl("questionGroupsAdministration/add/", $paramArray);
                $saveAndAddQuestionLink = $this->createUrl("questionAdministration/view/", $paramArray);
            ?>

            <button class="btn btn-primary" id='save-and-new-question-button' href="<?php echo $saveAndAddQuestionLink ?>" type="button">
                <span class="ri-check-fill"></span>
                <?php eT("Save & add new question"); ?>
            </button>

            <button class="btn btn-primary" id='save-and-new-button' href="<?php echo $saveAndNewLink ?>" type="button">
                <span class="ri-check-fill"></span>
                <?php eT("Save & add new group"); ?>
            </button>
        <?php endif; ?>
        <?php if (isset($surveybar['importquestion'])):?>
            <?php
                //Save and new button
                $paramArray = array();
                $paramArray["surveyid"] = $surveyid;

                if (isset($gid) && !empty($gid)) {
                    $paramArray["gid"] = $gid;
                }

                $saveAndNewLink = $this->createUrl("questionAdministration/view/", $paramArray);
            ?>

            <button class="btn btn-primary" id='save-and-new-button' href="<?php echo $saveAndNewLink ?>" type="button">
                <span class="ri-check-fill"></span>
                <?php eT("Save and new"); ?>
            </button>
        <?php endif; ?>
    <?php endif; ?>
</div>
