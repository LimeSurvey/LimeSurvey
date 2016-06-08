<?php

/**
 * Array by column
 *
 * @var $anscount
 * @var $cellwidth
 */
 ?>

<table class="array-by-columns-table table-array-by-column table question subquestion-list questions-list">
    <colgroup class="col-responses">
        <col class="col-answers" style='width: 50%' />

        <?php for ($i = 0; $i < $anscount; $i++): ?>
            <col class="question-item answers-list radio-list <?php echo ($i % 2 == 0 ? "odd well" : "even"); ?>" style='width: <?php echo $cellwidth; ?>%;' />
        <?php endfor; ?>

    </colgroup>
    <thead class='thead-array-by-column'>
        <tr>
            <td>&nbsp;</td>

            <?php foreach ($aQuestions as $question): ?>
                <?php if ($question['errormandatory']): ?>
                    <th class='text-center'>
                        <div class="label label-danger" role="alert">
                            <?php echo $question['question']; ?>
                        </div>
                    </th>
                <?php else: ?>
                    <th class="text-center">
                        <?php echo $question['question']; ?>
                    </th>
                <?php endif; ?>
            <?php endforeach; ?>

        </tr>
    </thead>
    <tbody>
        <?php foreach ($labels as $ansrow): ?>
            <tr>
                <th class="arraycaptionleft dontread">
                    <?php echo $ansrow['answer']; ?>
                </th>
                <?php foreach ($anscode as $i => $ld): ?>
                    <td class="answer-cell-7 answer_cell_<?php echo $ld; ?> answer-item radio-item text-center radio">
                            <input
                                class="radio"
                                type="radio"
                                name="<?php echo $aQuestions[$i]['myfname']; ?>"
                                value="<?php echo $ansrow['code']; ?>"
                                id="answer<?php echo $aQuestions[$i]['myfname']; ?>-<?php echo $ansrow['code']; ?>"
                                <?php echo $checked[$ansrow['code']][$ld]; ?>
                                onclick="<?php echo $checkconditionFunction; ?>(this.value, this.name, this.type)"
                            />
                        <label for="answer<?php echo $aQuestions[$i]['myfname']; ?>-<?php echo $ansrow['code']; ?>">
                            <span class="visible-xs-block label-text"><?php echo $ansrow['answer'];?></span>
                        </label>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>

</table>

<?php foreach ($anscode as $i => $ld): ?>
    <input
        type="hidden"
        name="java<?php echo $aQuestions[$i]['myfname']; ?>"
        id="java<?php echo $aQuestions[$i]['myfname']; ?>"
        value="<?php echo $aQuestions[$i]['myfname_value']; ?>"
    />
<?php endforeach; ?>
