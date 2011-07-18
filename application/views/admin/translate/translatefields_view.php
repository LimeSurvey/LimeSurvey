<?php if (strlen(trim((string)$textfrom)) > 0)
{
  $all_fields_empty = FALSE;
  $evenRow = !($evenRow);
  // Display translation fields
  echo translate::displayTranslateFields($surveyid, $gid, $qid, $type,
          $amTypeOptions, $baselangdesc, $tolangdesc, $textfrom, $textto, $i, $rowfrom, $evenRow);
  if ($associated && strlen(trim((string)$textfrom2)) > 0)
  {
    $evenRow = !($evenRow);
    echo translate::displayTranslateFields($surveyid, $gid, $qid, $type2,
            $amTypeOptions2, $baselangdesc, $tolangdesc, $textfrom2, $textto2, $i, $rowfrom2, $evenRow);
  }
}
else
{ ?>
    <input type='hidden' name='<?php echo $type;?>_newvalue[<?php echo $i;?>]' value='<?php echo $textto;?>' />
<?php } ?>