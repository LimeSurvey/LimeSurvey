<?php if ($andOrOr): ?>
    <h4 class='condition-and-or-or my-2'><span class='badge'> <?php echo $andOrOr; ?> </span></h4>
<?php endif; ?>

<?php echo TbHtml::form(
    $formAction,
    'post',
    array(
        'id'=>"conditionaction" . $row['cid'],
        'name'=>"conditionaction" . $row['cid']
    )
); ?>
    <table class='table conditions-table'>
        <tr class='active'>
            <?php if ($subaction == "copyconditionsform" || $subaction == "copyconditions" ): ?>
                <td></td>
                <td class='scenariotd'>
                    <input 
                        type='checkbox'
                        name='aConditionFromScenario<?php echo $scenarionr['scenario']; ?>'
                        id='cbox<?php echo $row['cid']; ?>'
                        value='<?php echo $row['cid']; ?>'
                        checked='checked'
                    />
                </td>
            <?php endif; ?>

            <td class='col-lg-4 questionnamecol'>
                <span><?php echo CHtml::encode($name); ?></span>
            </td>
            <td class='col-lg-2 operatornametd'>
                <span><?php echo $method[trim((string) $row['method'])]; ?> </span>
            </td>
            <td class='col-lg-3 questionanswertd'>
                <span><?php echo $target; ?></span>
            </td>
            <td class='text-end'>
                <?php echo $editButtons; ?>
                <?php echo $hiddenFields; ?>
            </td>
        </tr>
    </table>
</form>
