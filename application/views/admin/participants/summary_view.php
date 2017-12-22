<?php
/* @var $this AdminController */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('participantsSummary');

?>
<div id="pjax-content">
    <div class="col-lg-12 list-surveys">
        <h3><?php eT("Central participants database summary"); ?></h3>

        <div class="row">
            <div class="col-lg-12 content-right">
    <table class='statisticssummary table table-striped'>
        <tr>
            <th>
                <?php eT("Total participants in central table"); ?>
            </th>
            <td>
                <?php echo $totalrecords; ?>
            </td>
        </tr>
        <tr>
            <th>
                <?php eT("Participants owned by you"); ?>
            </th>
            <td>
                <?php echo $owned . ' / ' . $totalrecords; ?>
            </td>
        </tr>
        <tr>
            <th>
                <?php eT("Participants shared with you"); ?>
            </th>
            <td>
                <?php echo $totalrecords - $owned . ' / ' . $totalrecords; ?>
            </td>
        </tr>
        <tr>
            <th>
                <?php eT("Participants you have shared"); ?>
            </th>
            <td>
                <?php echo $shared . ' / ' . $totalrecords; ?>
            </td>
        </tr>
        <tr>
            <th>
                <?php eT("Blacklisted participants"); ?>
            </th>
            <td>
                <?php echo $blacklisted; ?>
            </td>
        </tr>
        <tr>
            <th>
                <?php eT("Total attributes in the central table"); ?>
            </th>
            <td>
                <?php echo $attributecount; ?>
            </td>
        </tr>
    </table>            
            </div>
        </div>
    </div>   
</div>   

