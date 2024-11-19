<?php
/**
 * @var $id int
 * @var $surveyid int
 * @var $answers array
 * @var $fnames array
 * @var $inserthighlight string
 */
?>

<div class='side-body'>
    <h3><?php echo sprintf(gT("View response ID %d"), $id); ?></h3>
    <div class="row">
        <div class="col-12 content-right">
            <?php echo CHtml::form(["responses/browse/", ['surveyId' => $surveyid]], 'post', ['id' => 'resulttableform']); ?>
            <input id='downloadfile' name='downloadfile' value='' type='hidden'>
            <input id='sid' name='sid' value='<?php echo $surveyid; ?>' type='hidden'>
            <input id='subaction' name='subaction' value='all' type='hidden'>
            <?php echo CHtml::endForm() ?>

            <table class='detailbrowsetable table table-striped'>

                <?php foreach ($answers as $answer) : ?>
                    <?php if (!isset($fnames[$answer['i']]['type']) ||
                        (isset($fnames[$answer['i']]['type']) && $fnames[$answer['i']]['type'] !== '|') ||
                        (isset($fnames[$answer['i']]['type']) && $fnames[$answer['i']]['type'] === '|' && $answer['answervalue'] !== '')
                    ) : ?>
                        <tr <?php echo $inserthighlight; ?>>
                            <th>
                                <?php if (isset($fnames[$answer['i']]['code'])) { ?>
                                    [<strong class="qcode"><?php echo $fnames[$answer['i']]['code']; ?></strong>]
                                <?php } ?>
                                <?php echo strip_tags((string) stripJavaScript($fnames[$answer['i']][1])); ?></th>
                            <td>
                                <?php
                                echo $answer['answervalue']; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
