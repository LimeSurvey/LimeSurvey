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
    <ul class="list-unstyled ls-js-hidden-sr answers-list select-list " role="group" aria-labelledby="ls-question-text-<?php echo $basename; ?>">
        <?php
            // rows/answer_row.php
            echo  $sSelects;
        ?>
    </ul>
    <div class="ls-no-js-hidden answers-list<?php echo ($samechoiceheight) ? " list-samechoiceheight": "" ?><?php echo ($samelistheight) ? " list-samelistheight": "" ?> row" aria-hidden="true">
        <div class="col-sm-6 col-xs-6">
            <strong class="sortable-subtitle sortable-rank-subtitle"><?php echo $rank_title;?></strong>
            <!-- @todo : move htmlblock at the good place -->
            <ul id="sortable-choice-<?php echo $qId;?>" class="sortable-choice sortable-list list-group">
                <?php foreach ($answers as $ansrow):?>
                    <li id="javatbd<?php echo $rankingName; ?><?php echo $ansrow['code'];?>" class="ls-choice list-group-item answer-item sortable-item sortable-enable" data-value="<?php echo $ansrow['code'];?>">
                        <?php echo $ansrow['answer']; ?>
                    </li>
                <?php endforeach;?>
                <li class="hidden ls-remove"></li>
            </ul>
        </div>
        <div class="col-sm-6 col-xs-6">
            <strong class="sortable-subtitle sortable-rank-subtitle"><?php echo $choice_title;?></strong>
            <ul id="sortable-rank-<?php echo $qId;?>" class="sortable-rank sortable-list list-group">
                <li class="hidden ls-remove"></li>
            </ul>
        </div>
    </div>
</div>

<!--  The list with HTML answers -->

<div class="hidden" style="display:none">
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
<!-- end of answer -->
