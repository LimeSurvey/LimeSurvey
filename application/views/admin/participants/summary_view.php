<?php
/**
 * @var AdminController $this
 * @var string $totalrecords
 * @var string $owned
 * @var string $shared
 * @var string $blacklisted
 * @var string $attributecount
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('participantsSummary');

?>
<div id="pjax-content">
    <div class="col-12 list-surveys">
        <div class="row">
            <div class="col-12 content-right table-responsive">
                <table class='ls-statisticssummary table table-hover'>
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
                            <?php eT("Blocklisted participants"); ?>
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

