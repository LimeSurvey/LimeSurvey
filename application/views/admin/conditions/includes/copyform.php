<?php echo CHtml::form($url, 'post', array('id'=>"copyconditions",'name'=>"copyconditions")); ?>
    <h3><?php eT("Copy conditions"); ?></h3>

    <?php if (count($conditionsList)): ?>
        <script type='text/javascript'>
            $(document).ready(function () {
                // TODO
                // $('#copytomultiselect').multiselect({ autoOpen: true, noneSelectedText: '".gT("No questions selected")."', checkAllText: '".gT("Check all")."', uncheckAllText: '".gT("Uncheck all")."', selectedText: '# ".gT("selected")."', beforeclose: function(){ return false;},height: 200 } ); });
        });
        </script>

        <div class='conditioncopy-tbl-row'>
            <div class='condition-tbl-left'><?php eT("Copy the selected conditions to:"); ?></div>
            <div class='condition-tbl-right'>
                <select class='form-select' name='copyconditionsto[]' id='copytomultiselect'  multiple='multiple' >

                    <?php foreach ($pquestions as $pq): ?>
                        <option value='<?php echo $pq['fieldname']; ?>'><?php echo $pq['text']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class='condition-tbl-full'>
            <br/>
            <input class='btn btn-outline-secondary' type='submit' value='<?php eT("Copy conditions"); ?>' onclick="prepareCopyconditions(); return true;" />
            <input type='hidden' name='subaction' value='copyconditions' />
            <input type='hidden' name='sid' value='<?php echo $iSurveyID; ?>' />
            <input type='hidden' name='gid' value='<?php echo $gid; ?>' />
            <input type='hidden' name='qid' value='<?php echo $qid; ?>' />
        </div>

    <?php else: ?>
        <div class='messagebox ui-corner-all'>
            <div class='partialheader'>
                <?php eT("There are no existing conditions in this survey."); ?>
            </div>
        </div>
    <?php endif; ?>

</form>

<script>
function prepareCopyconditions() {
    $('input:checked[name^=\"aConditionFromScenario\"]').each(function(i,val) {
        var thecid = val.value;
        var theform = document.getElementById('copyconditions');
        window.LS.addHiddenElement(theform,'copyconditionsfrom[]',thecid);
        return true;
    });
}
</script>
