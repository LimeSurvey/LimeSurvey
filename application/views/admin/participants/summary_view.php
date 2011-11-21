<div class='header ui-widget-header'>
  <?php echo $clang->gT("Central participants database summary");?>
</div>
<br />
<table align='center' class='statisticssummary'>
 <tr>
  <th>
    <?php echo $clang->gT("Total participants in central table");?>
  </th>
  <td>
    <?php echo $totalrecords; ?>
  </td>
 </tr>
 <tr>
  <th>
    <?php echo $clang->gT("Participants owned by you"); ?>
  </th>
  <td>
    <?php echo $owned.' / '.$totalrecords; ?>
  </td>
 </tr>
 <tr>
  <th>
    <?php echo $clang->gT("Participants shared with you");?>
  </th>
  <td>
    <?php echo $totalrecords-$owned.' / '.$totalrecords; ?>
  </td>
 </tr>
 <tr>
  <th>
    <?php echo $clang->gT("Participants you have shared");?>
  </th>
  <td>
    <?php echo $shared.' / '.$totalrecords; ?>
  </td>
 </tr>
 <tr>
  <th>
    <?php echo $clang->gT("Blacklisted participants");?>
  </th>
  <td>
    <?php echo $blacklisted; ?>
  </td>
 </tr>
 <tr>
  <th>
    <?php echo $clang->gT("Total attributes in the central table");?>
  </th>
  <td>
    <?php echo $attributecount; ?>
  </td>
 </tr>
</table>
<br />