<?php
/**
 * Array multiple flexible, column header HTML
 */
?>
<colgroup class="col-responses">
    <col class="answertext" />
        <col class="answertext" />
            <th >&nbsp;</th>
            <?php foreach($labelans as $ld): ?>
                <th  class='th-11'>
                    <?php echo $ld; ?>
                </th>
                <col class="<?php echo $odd_even;?>" />
                <col class="<?php echo $odd_even; ?>" />
            <?php endforeach;?>
            foreach ($labelans as $ld)
            {
                $answer_head_line .= "\t
                $odd_even = alternation($odd_even);
                //$mycols .= "\n";
                $mycols .= "<col class=\"$odd_even\" />\n";
            }
