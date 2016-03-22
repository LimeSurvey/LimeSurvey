<?php
/**
 * JavaScript snippet for ranking question
 * TODO: create a js file, and move variable to data- HTML5 element in the HTML view.
 *
 * @var $qid                            $ia[0]
 * @var $choice_title
 * @var $rank_title
 * @var $rank_help
 * @var $showpopups                     $aQuestionAttributes["showpopups"]
 * @var $samechoiceheight               $aQuestionAttributes["samechoiceheight"]
 * @var $samelistheight                 $aQuestionAttributes["samelistheight"]
 */
?>
<script type='text/javascript'>
    <!--
    var aRankingTranslations = {
         choicetitle: '<?php echo $choice_title;?>',
         ranktitle: '<?php echo $rank_title;?>',
         rankhelp: '<?php echo $rank_help;?>'
        };

    doDragDropRank(<?php echo $qid; ?>, <?php echo $showpopups;?>,<?php echo $samechoiceheight;?>,<?php echo $samelistheight;?>);
    -->
</script>
