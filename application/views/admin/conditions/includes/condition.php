<?php echo $andOrOr; ?>

<table class='table conditionstable'>
    <tr class='active'>

        <?php if ($subaction == "copyconditionsform" || $subaction == "copyconditions" ): ?>
            <td></td>
            <td class='scenariotd'>
                <input type='checkbox' name='aConditionFromScenario<?php echo $scenarionr['scenario']; ?>' id='cbox<?php echo $row['cid']; ?>' value='<?php echo $row['cid']; ?>' checked='checked'/>
            </td>
        <?php endif; ?>

        <td class='col-md-4 questionnamecol'>
            <span>

