<?php
/**
 * @var AdminController $this
 * @var Survey $oSurvey
 * @var array $queries
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('surveyParticipantsIndex');

?>
<div class='side-body <?php echo getSideBodyClass(true); ?>'>
    <h3><?php eT("Survey participant summary"); ?></h3>

    <div class="row">
        <div class="col-lg-4 content-right">
            <table class="items table table-striped"  >
                <tr>
                    <th>
                        <?php eT("Total records"); ?>
                    </th>
                    <td>
                        <?php echo $queries['count']; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php eT("Total with no unique token"); ?>
                    </th>
                    <td>
                        <?php echo $queries['invalid']; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php eT("Total invitations sent"); ?>
                    </th>
                    <td>
                        <?php echo $queries['sent']; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php eT("Total opted out"); ?>
                    </th>
                    <td>
                        <?php echo $queries['optout']; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php eT("Total screened out"); ?>
                    </th>
                    <td>
                        <?php echo $queries['screenout']; ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <?php eT("Total surveys completed"); ?>
                    </th>
                    <td>
                        <?php echo $queries['completed']; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</div>
