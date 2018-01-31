<?php

/**
 * Subview of surveybar_view.
 * @param $oSurvey
 * @param $respstatsread
 * @param $respstatsread
 * @param $responsescreate
 */

?>

<div class="btn-group">
    <!-- main  dropdown header -->
    <?php if ($oSurvey->isActive):?>
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="icon-responses"></span>
            <?php eT("Responses"); ?><span class="caret"></span>
        </button>
    <?php else:?>
        <button type="button" data-toggle="tooltip" data-placement="bottom" title="<?php eT("This survey is not active - no responses are available."); ?>" class="readonly btn btn-default">
            <span class="icon-responses"></span>
            <?php eT("Responses"); ?><span class="caret"></span>
        </button>
    <?php endif; ?>

    <!-- dropdown -->
    <ul class="dropdown-menu">
        <?php if ($respstatsread && $oSurvey->isActive):?>
            <!-- Responses & statistics -->
            <li>
                <a class="pjax" href='<?php echo $this->createUrl("admin/responses/sa/index/surveyid/$oSurvey->sid/");?>' >
                    <span class="icon-browse"></span>
                    <?php eT("Responses & statistics"); ?>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($responsescreate && $oSurvey->isActive): ?>
            <!-- Data entry screen -->
            <li>
                <a href='<?php echo $this->createUrl("admin/dataentry/sa/view/surveyid/$oSurvey->sid"); ?>' >
                    <span class="fa fa-keyboard-o"></span>
                    <?php eT("Data entry screen"); ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($responsesread && $oSurvey->isActive): ?>
            <!-- Partial (saved) responses -->
            <li>
                <a href='<?php echo $this->createUrl("admin/saved/sa/view/surveyid/$oSurvey->sid"); ?>' >
                    <span class="icon-saved"></span>
                    <?php eT("Partial (saved) responses"); ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>
