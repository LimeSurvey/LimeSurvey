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
<div class="ranking-answers">
    <ul class="list-unstyled answers-list select-list">
        <?php
            // rows/answer_row.php
            echo  $sSelects;
        ?>
    </ul>

    <div style='display:none' id='ranking-<?php echo $rankId;?>-maxans'>
        <?php echo $max_answers;?>
    </div>

    <div style='display:none' id='ranking-<?php echo $rankId;?>-minans'>
        <?php echo $min_answers;?>
    </div>

    <div style='display:none' id='ranking-<?php echo $rankId;?>-name'>
        <?php echo $rankingName;?>
    </div>
</div>

<!--  The list with HTML answers -->
<div style="display:none">
    <?php foreach ($answers as $ansrow):?>
        <div id="htmlblock-<?php echo $rankId;?>-<?php echo $ansrow['code'];?>">
            <?php echo $ansrow['answer']; ?>
        </div>
    <?php endforeach;?>
</div>


<script type='text/javascript'>
    <!--
    var aRankingTranslations = {
         choicetitle: '<?php echo $choice_title;?>',
         ranktitle: '<?php echo $rank_title;?>',
         rankhelp: '<?php echo $rank_help;?>'
        };

    doDragDropRank(<?php echo $rankId; ?>, <?php echo $showpopups;?>,<?php echo $samechoiceheight;?>,<?php echo $samelistheight;?>);
    -->
</script>
<!-- end of answer -->
