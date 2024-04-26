<?php
/**
 * Result of a token post action
 */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <?php $this->widget('ext.admin.survey.PageTitle.PageTitle', array(
        'title' => gT("Adding survey participant entry…"),
        'model' => $oSurvey,
    )); ?>
    <div class="row">
        <div class="col-12 content-right">
            <!-- Success -->
            <?php if ($success): ?>
                <div class="jumbotron message-box">
                    <p class="lead text-success"><?php eT("Success"); ?></p>
                    <p><?php eT("New entry was added."); ?></p>
                    <div class="container">
                        <div class="col-12 col-xl-4 offset-xl-2">
                            <input class="btn btn-large btn-block btn-outline-secondary" type='button' value='<?php eT("Browse survey participants"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>', '_top')" />
                        </div>
                        <div class="col-12 col-xl-4">
                            <input class="btn btn-large btn-block btn-outline-secondary" type='button' value='<?php eT("Add another participant"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>', '_top')" />
                        </div>
                    </div>
                </div>

            <!-- Fail -->
            <?php else:?>
                <div class="jumbotron message-box message-box-error">
                    <p class="lead text-danger"><?php eT("Failed"); ?></p>
                    <?php foreach ($errors as $error): ?>
                        <p class='lead'><?php echo $error[0]; ?></p>
                    <?php endforeach; ?>
                    <div class="container">
                        <div class="col-12 col-xl-4 offset-xl-2">
                            <input type='button' class="btn btn-large btn-outline-secondary" value='<?php eT("Browse survey participants"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/browse/surveyid/$surveyid"); ?>', '_top')" /><br />
                        </div>
                        <div class="col-12 col-xl-4 offset-xl-2">
                            <input type='button' class="btn btn-large btn-outline-secondary" value='<?php eT("Add new survey participant"); ?>' onclick="window.open('<?php echo $this->createUrl("admin/tokens/sa/addnew/surveyid/$surveyid"); ?>', '_top')" /><br />
                        </div>
                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>
