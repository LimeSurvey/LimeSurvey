<?php
/**
 * Ranking question, item list header Html
 * @var $sOptions         : the select options, generated with the view answer_row.php
 *
 * @var $name
 * @var $myfname
 * @var $labeltext
 * @var $rankId
 * @var $rankingName
 * @var $max_answers
 * @var $min_answers
 * @var $qid
 * @var $choice_title
 * @var $rank_title
 * @var $rank_help
 * @var $showpopups
 * @var $samechoiceheight
 * @var $samelistheight
 */
?>

<!-- Ranking -->

<!-- answer -->
<div class="<?php echo $coreClass; ?>">
    <ul class="list-unstyled ls-js-hidden-sr answers-list select-list form-horizontal">
        <?php
            // rows/answer_row.php
            echo  $sSelects;
        ?>
    </ul>
    <div class="dragDropTable ls-no-js-hidden answers-list<?php echo ($samechoiceheight) ? " list-samechoiceheight": "" ?><?php echo ($samelistheight) ? " list-samelistheight": "" ?> row" aria-hidden="true">
        <div class="col-sm-6 col-xs-6">
            <strong class="SortableTitle"><?php echo $rank_title;?></strong>
            <!-- @todo : move htmlblock at the good place -->
            <div class="dragDropChoices">
                <ul id="sortable-choice-<?php echo $qId;?>" class="connectedSortable<?php echo $qId;?> sortable-list list-unstyled">
                    <li>&nbsp;</li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 col-xs-6">
            <strong class="SortableTitle"><?php echo $choice_title;?></strong>
            <div class="dragDropRanks">
                <ul id="sortable-rank-<?php echo $qId;?>" class="connectedSortable<?php echo $qId;?> sortable-list selectionSortable  list-unstyled">
                    <li>&nbsp;</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!--  The list with HTML answers -->

<div class="hidden" style="display:none">
    <?php foreach ($answers as $ansrow):?>
        <div id="htmlblock-<?php echo $qId;?>-<?php echo $ansrow['code'];?>">
            <?php echo $ansrow['answer']; ?>
        </div>
    <?php endforeach;?>
    <div style='display:none' id='ranking-<?php echo $qId;?>-maxans'>
        <?php echo $max_answers;?>
    </div>
    <div style='display:none' id='ranking-<?php echo $qId;?>-minans'>
        <?php echo $min_answers;?>
    </div>
    <div style='display:none' id='ranking-<?php echo $qId;?>-name'>
        <?php echo $rankingName;?>
    </div>

</div>


<script type='text/javascript'>
    <!--
    var aRankingTranslations = {
         rankhelp: '<?php echo $rank_help;?>'
        };

    doDragDropRank(<?php echo $qId; ?>, <?php echo $showpopups;?>,<?php echo $samechoiceheight;?>,<?php echo $samelistheight;?>);
    -->
</script>
<!-- end of answer -->
