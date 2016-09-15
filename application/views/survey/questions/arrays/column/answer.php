<?php

/**
 * Array by column
 *
 * @var $anscount
 * @var $cellwidth
 */
 ?>
<div class="no-more-tables">
    <table class="table question subquestion-list questions-list">
        <thead class=' array1 dontread'>
            <tr>
                <th>&nbsp;</th>
                <?php
                foreach ($aQuestions as $i=>$question): ?>
                    <?php if ($question['errormandatory']): ?>
                        <th class='text-center<?php echo ($i % 2 == 0)?' array2':' well';?>'>
                            <span class="label label-danger" role="alert">
                                <?php echo $question['question']; ?>
                            </span >
                        </th>
                    <?php else: ?>
                        <th class="text-center<?php echo ($i % 2 == 0)?' array2':' well';?>">
                            <?php echo $question['question']; ?>
                        </th>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($labels as $ansrow): ?>
                <tr id="javatbd<?php echo $ansrow['code'];?>" class="answers-list radio-list">
                    <th class="answertext">
                        <span class="answertextright"><?php echo $ansrow['answer']; ?></span>
                    </th>
                    <?php
                    foreach ($anscode as $i => $ld): ?>
                        <td class="answer-cell-2 answer_cell_<?php echo $ld;?><?php echo ($i % 2 == 0)?' array2':' well';?> answer-item radio-item radio text-center">
                                <input
                                    class="radio"
                                    type="radio"
                                    name="<?php echo $aQuestions[$i]['myfname']; ?>"
                                    value="<?php echo $ansrow['code']; ?>"
                                    id="answer<?php echo $aQuestions[$i]['myfname']; ?>-<?php echo $ansrow['code']; ?>"
                                    <?php echo $checked[$ansrow['code']][$ld]; ?>
                                    onclick='<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)' />
                            <label class="" for="answer<?php echo $aQuestions[$i]['myfname']; ?>-<?php echo $ansrow['code']; ?>">
                                <span class="visible-xs-inline-block label-clickable label-text"><?php echo $aQuestions[$i]['question'];?></span>
                            </label>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php foreach ($anscode as $i => $ld): ?>
    <input
        type="hidden"
        name="java<?php echo $aQuestions[$i]['myfname']; ?>"
        id="java<?php echo $aQuestions[$i]['myfname']; ?>"
        value="<?php echo $aQuestions[$i]['myfname_value']; ?>"
    />
<?php endforeach; ?>
