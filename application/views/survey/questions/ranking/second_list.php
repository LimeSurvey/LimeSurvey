<?php
/**
 * Ranking question, Html between the two lists of items
 * @var $rankId         $ia[0]
 * @var $rankingName               $ia[1]
 * @var $max_answers
 * @var $min_answers
 */
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
