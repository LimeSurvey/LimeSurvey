<?php

/**
 * Dual-scale array with dropdown representation

 * @var $answerwidth
 * @var $cellwidth
 * @var $ddprefix
 * @var $ddsuffix
 */

?>

<table class="<?php echo $coreClass; ?> table table-bordered table-hover" role="group" aria-labelledby="ls-question-text-<?php echo $basename ?>">

<!-- Column groups -->
<colgroup>
    <col class="answertext" style='width: <?php echo $answerwidth; ?>%;' />
    <col class="dsheader" style='width: <?php echo $cellwidth; ?>%;' />
    <col class="ddarrayseparator" style='width: <?php echo $separatorwidth; ?>%'/>
    <col class="dsheader"  style='width: <?php echo $cellwidth; ?>%;' />
</colgroup>
<!-- Header -->
<?php if ($leftheader != '' || $rightheader !=''): ?>
    <thead>
        <tr class="ls-heading">
            <td></td>
            <th class='left-header'><?php echo $leftheader; ?></th>
            <td></td>
            <th class='right-header'><?php echo $rightheader; ?></th>
        </tr>
    </thead>
<?php endif; ?>

<tbody>

    <!-- Sub questions -->

    <?php foreach ($aSubQuestions as $ansrow): ?>
        <!-- <tr> -->
        <tr id="javatbd<?php echo $ansrow['myfname']; ?>" role="group" class="answers-list radio-list <?php echo ($ansrow['odd']) ? "ls-odd" : "ls-even"; ?><?php if($ansrow['mandatoryviolation']){ echo " ls-error-mandatory";} ?>">
            <!-- Answer text (actual question) -->

            <th id="answertext<?php echo $ansrow['myfname']; ?>" class="answertext control-label<?php echo ($ansrow['mandatoryviolation']) ? " text-danger" : ""; ?>">
                <label class="control-label" for="answer<?php echo $ansrow['myfid0']; ?>" id="label-<?php echo $ansrow['myfname']; ?>">
                    <?php echo $ansrow['question']; ?>
                </label>
                <?php
                /* Value for expression manager javascript (use id) ; no need to submit */
                echo \CHtml::hiddenField("java{$ansrow['myfid0']}",$ansrow['sActualAnswer0'],array(
                    'id' => "java{$ansrow['myfid0']}",
                    'disabled' => true,
                ));
                echo \CHtml::hiddenField("java{$ansrow['myfid1']}",$ansrow['sActualAnswer1'],array(
                    'id' => "java{$ansrow['myfid1']}",
                    'disabled' => true,
                ));
                ?>
            </th>

            <!-- First dropdown -->

            <td class="answer-item dropdown-item<?php echo ($ansrow['mandatoryviolation'] && $ansrow['sActualAnswer0']==='') ? " has-error" : ""; ?>">
                <?php if ($leftheader != '') : ?>
                    <div  class='visible-xs leftheader control-label'><?php echo $leftheader; ?></div>
                <?php endif; ?>
                <?php if ($ddsuffix != '' || $ddprefix != ''): ?>
                    <div class="ls-input-group">
                <?php endif; ?>
                <?php if ($ddprefix != ''): ?>
                    <div class="ddprefix ls-input-group-extra">
                        <?php echo $ddprefix; ?>
                    </div>
                <?php endif; ?>
                <select
                    class='form-control'
                    name="<?php echo $ansrow['myfname0']; ?>"
                    id="answer<?php echo $ansrow['myfid0']; ?>"
                    aria-labelledby="answertext<?php echo $ansrow['myfname']; ?>"
                >
                    <!-- Please choose... -->
                    <?php if ($ansrow['sActualAnswer0'] === ''): ?>
                        <option value="" <?php echo SELECTED; ?> >
                            <?php eT('Please choose...'); ?>
                        </option>
                    <?php endif; ?>

                    <!-- First label set -->
                    <?php foreach ($labels0 as $lrow): ?>
                        <option
                            value="<?php echo $lrow['code']; ?>"
                            <?php if ($ansrow['sActualAnswer0'] == $lrow['code']): echo SELECTED; endif; ?>
                        >

                            <?php echo flattenText($lrow['title']); ?>

                        </option>
                    <?php endforeach; ?>

                    <!-- No answer -->
                    <?php if ($ansrow['showNoAnswer0'] && $ansrow['sActualAnswer0'] != ''): ?>
                        <option value=""><?php eT('No answer'); ?></option>
                    <?php endif; ?>
                </select>
                <?php if ($ddsuffix != ''): ?>
                    <div class="ddsuffix ls-input-group-extra">
                        <?php echo $ddsuffix; ?>
                    </div>
                <?php endif; ?>
                <?php if ($ddsuffix != '' || $ddprefix != ''): ?>
                    </div>
                <?php endif; ?>
            </td>


            <!-- Separator -->

            <td class="ddarrayseparator information-item"><?php echo $interddSep; ?></td>

            <!-- Second dropdown -->

            <td class="answer-item dropdown-item<?php echo ($ansrow['mandatoryviolation'] && $ansrow['sActualAnswer1']==='') ? " has-error" : ""; ?>">
                <?php if ($rightheader != '') : ?>
                    <div  class='visible-xs rightheader control-label'><?php echo $rightheader; ?></div>
                <?php endif; ?>
                <!-- We don't need another label : aria-labelledby for accessibility, and we have only 2 line in phone and no-more-table -->
                <?php if ($ddprefix != '' || $ddsuffix != ''): ?>
                    <div class="ls-input-group">
                <?php endif; ?>
                <?php if ($ddprefix != ''): ?>
                    <div class="ddprefix ls-input-group-extra"><?php echo $ddprefix; ?></div>
                <?php endif; ?>
                <select class='form-control' name="<?php echo $ansrow['myfname1']; ?>" id="answer<?php echo $ansrow['myfid1']; ?>" aria-labelledby="label-<?php echo $ansrow['myfname']; ?>">
                    <!-- Please choose... -->
                    <?php if ($ansrow['sActualAnswer1'] === ''): ?>
                        <option value="" <?php echo SELECTED; ?> ><?php eT('Please choose...'); ?></option>
                    <?php endif; ?>

                    <!-- Second label set -->
                    <?php foreach ($labels1 as $lrow): ?>
                        <option
                            value="<?php echo $lrow['code']; ?>"
                            <?php if ($ansrow['sActualAnswer1'] == $lrow['code']): echo SELECTED; endif; ?>
                        >
                            <?php echo flattenText($lrow['title']); ?>

                        </option>
                    <?php endforeach; ?>

                    <!-- No answer -->
                    <?php if ($ansrow['showNoAnswer1'] && $ansrow['sActualAnswer1'] != ''): ?>
                        <option value=""><?php eT('No answer'); ?></option>
                    <?php endif; ?>

                </select>
                <?php if ($ddsuffix != ''): ?>
                    <div class="ddsuffix ls-input-group-extra"><?php echo $ddsuffix; ?></div>
                <?php endif; ?>
                <?php if ($ddprefix != '' || $ddsuffix != ''): ?>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

</tbody>

</table>

