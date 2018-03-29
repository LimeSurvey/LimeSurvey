<?php
/**
 * @var $this AdminController
* Response Summary view
*/

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyResponsesIndex');

?>
<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <h3><?php eT("Response summary"); ?></h3>
    <div class="row">
        <div class="col-lg-12 content-right">
            <table class='statisticssummary table'>
                <tbody>
                    <tr><th><?php eT("Full responses"); ?></th><td><?php echo $num_completed_answers; ?></td></tr>
                    <tr><th><?php eT("Incomplete responses"); ?></th><td><?php echo ($num_total_answers - $num_completed_answers); ?></td></tr>
                </tbody>
                <tr><th><?php eT("Total responses"); ?></th><td><?php echo $num_total_answers; ?></td></tr>
            </table>
        </div>
    </div>
    <?php if(isset($with_token)): ?>
        <h3><?php eT("Survey participant summary"); ?></h3>
        <div class="row">
            <div class="col-lg-12 content-right">
                <table class='statisticssummary table'>
                    <tbody>
                        <tr><th><?php eT("Total invitations sent"); ?></th><td><?php echo $tokeninfo['sent']; ?></td></tr>
                        <tr><th><?php eT("Total surveys completed"); ?></th><td><?php echo $tokeninfo['completed']; ?></td></tr>
                        <tr><th><?php eT("Total with no unique token"); ?></th><td><?php echo $tokeninfo['invalid'] ?></td></tr>
                    </tbody>
                    <tr><th><?php eT("Total records"); ?></th><td><?php echo $tokeninfo['count']; ?></td></tr>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
