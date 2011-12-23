<div class='header ui-widget-header'>
    <?php $clang->eT("Central participants database summary"); ?>
</div>
<br />
<table align='center' class='statisticssummary'>
    <tr>
        <th>
            <?php $clang->eT("Total participants in central table"); ?>
        </th>
        <td>
            <?php echo $totalrecords; ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php $clang->eT("Participants owned by you"); ?>
        </th>
        <td>
            <?php echo $owned . ' / ' . $totalrecords; ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php $clang->eT("Participants shared with you"); ?>
        </th>
        <td>
            <?php echo $totalrecords - $owned . ' / ' . $totalrecords; ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php $clang->eT("Participants you have shared"); ?>
        </th>
        <td>
            <?php echo $shared . ' / ' . $totalrecords; ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php $clang->eT("Blacklisted participants"); ?>
        </th>
        <td>
            <?php echo $blacklisted; ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php $clang->eT("Total attributes in the central table"); ?>
        </th>
        <td>
            <?php echo $attributecount; ?>
        </td>
    </tr>
</table>
<br />
