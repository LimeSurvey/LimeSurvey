<?php
/**
 * This view display the header of the generated statistics
 * It is called from statistics_helper::generate_html_chartjs_statistics()
 *
 * @var $results
 * @var $total
 * @var $percent
 * @var $browse
 * @var $surveyid
 * @var $sql
 */
?>

<!-- Message Box -->
<div style="clear: both; margin-bottom: 10px;"></div><div class="jumbotron message-box">
    <h2><?php eT("Results"); ?></h2>
    <p><?php eT("Number of records in this query:")?>&nbsp;<?php echo $results;?></p>
    <p><?php eT("Total records in survey:"); ?>&nbsp;<?php echo $total; ?></p>

    <?php if($total): ?>
        <p><?php eT("Percentage of total:"); ?>&nbsp;<?php echo $percent; ?>%</p>
    <?php endif;?>

    <?php if($browse): ?>
        <?php echo CHtml::form(array("admin/responses/sa/browse/surveyid/{$surveyid}"), 'post')."\n" ;?>
            <p>
                <?php Yii::app()->user->setState('sql_'.$surveyid,$sql); ?>
                <input type='submit' class='btn btn-default' value='<?php eT("Browse");?>' />
                <input type='hidden' name='subaction' value='all'/>
            </p>
        </form>
    <?php endif;?>
</div>
<input type="hidden" id="showGraphOnPageLoad" />
