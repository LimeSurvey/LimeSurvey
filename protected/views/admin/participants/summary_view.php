<div class='header ui-widget-header'>
    <?php eT("Central participants database summary"); ?>
</div>
<br />
<table class='statisticssummary'>
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
<br />
