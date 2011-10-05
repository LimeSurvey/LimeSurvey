<?php
/* If there are any attributes to display as extra columns in the jqGrid, iterate through them */

/* Build a different colModel for the userid column based on whether or not the user is editable */
/* This can probably be moved into the controller */
if($this->config->item("userideditable") == 'Y')  //Firstly, if the user has edit rights, make the columns editable
{
	$uid = '{"name":"owner_uid", "index":"owner_uid", "width":150, "sorttype":"int", "sortable": true, "align":"center", "editable":true, "edittype":"select", "editoptions":{"value":"';
	$i=0;
	foreach($names->result() as $row)
	{
                $name[$i]=$row->uid.":".$row->full_name;
		$i++;
	}
	$unames = implode(";",$name).'"}}';
	$uidNames[] = $uid.$unames;
}
else
{
	$uidNames[] = '{"name":"owner_uid", "index":"owner_uid", "width":150, "sorttype":"int", "sortable": true, "align":"center", "editable":false}';
}
/* Build the options for additional languages */
$j=1;
$lang = '{"name":"language", "index":"language", "sorttype":"string", "sortable": true, "align":"center", "editable":true, "edittype":"select", "editoptions":{"value":"';
$getlangvalues = getLanguageData();
if($this->session->userdata('adminlang')!='auto')
{
$lname[0]=$this->session->userdata('adminlang').":".$getlangvalues[$this->session->userdata('adminlang')]['description'];
}
foreach ($getlangvalues as $keycode => $keydesc) {
                if($this->session->userdata('adminlang')!=$keycode)
                {
                        $cleanlangdesc = str_replace (";"," -",$keydesc['description']);
                        $lname[$j]=$keycode.":".$cleanlangdesc;
                        $j++;
                }
	}
	$langnames = implode(";",$lname).'"}}';
	$langNames[] = $lang.$langnames;
/* Build the columnNames for the extra attributes */
/* and, build the columnModel */
    if(isset($attributes) && count($attributes) > 0)
        {
        foreach($attributes as $row)
            {
                $attnames[]='"'.$row['attribute_name'].'"';
                $uidNames[]='{"name": "'.$row['attribute_name'].'", "index":"'.$row['attribute_name'].'", "sorttype":"string", "sortable": true, "align":"center"}';
            }
        $columnNames = ','.implode(",",$attnames).''; //Add to the end of the standard list of columnNames

    }
else
    {
      $columnNames = "";
    }
/* Build the javasript variables to pass to the jqGrid */
?>
<script type="text/javascript">
var spTitle = "<?php echo $clang->gT("Sharing participants..."); ?>";
var spAddBtn = "<?php echo $clang->gT("Share the selected participants"); ?>";
var sfNoUser = "<?php echo $clang->gT("No other user in the system"); ?>";
var addpartTitle = "<?php echo $clang->gT("Add participant to Survey"); ?>";
var addpartErrorMsg = "<?php echo $clang->gT("Either you don't own a survey or it doesn't have token table"); ?>";
var mapButton = "<?php echo $clang->gT("Next") ?>";
var error = "<?php echo $clang->gT("Error") ?>";
var addsurvey = "<?php echo $clang->gT("Add to survey") ?>";
var exportcsv = "<?php echo $clang->gT("Export CSV") ?>";
var nooptionselected = "<?php echo $clang->gT("Please choose either of the options") ?>";
var removecondition = "<?php echo $clang->gT("Remove condition") ?>";
var selectSurvey = "<?php echo $clang->gT("Please select a survey to add participants to"); ?>";
var cancelBtn = "<?php echo $clang->gT("Cancel") ?>";
var exportBtn = "<?php echo $clang->gT("Export") ?>";
var okBtn = "<?php echo $clang->gT("OK") ?>";
var deletefrompanelmsg = "<?php echo $clang->gT("Select one of the three options") ?>";
var noRowSelected = "<?php echo $clang->gT("You have no row selected") ?>";
var deletefrompanel = "<?php echo $clang->gT("Delete participant(s) from central participants panel only") ?>";
var deletefrompanelandtoken = "<?php echo $clang->gT("Delete participant(s) from central panel and tokens tables") ?>";
var deletefrompaneltokenandresponse = "<?php echo $clang->gT("Delete participant(s) from central panel, tokens tables and all associated responses") ?>";
var deleteMsg = "<br/>"+deletefrompanelmsg+"<br/><br/><center><ol id='selectable' class='selectable' ><li class='ui-widget-content' id='po'>"+deletefrompanel+"</li><li class='ui-widget-content' id='ptt'>"+deletefrompanelandtoken+"</li><li class='ui-widget-content' id='ptta'>"+deletefrompaneltokenandresponse+"</li></ol></center>";
var searchBtn = "<?php echo $clang->gT("Search") ?>";
var shareMsg = "<?php echo $clang->gT("You can see and edit settings for shared participant in share panel.") ?>"; //PLEASE REVIEW
var jsonUrl = "<?php echo site_url("admin/participants/getParticipants_json");?>";
var jsonSearchUrl = "<?php echo site_url("admin/participants/getParticipantsResults_json");?>";
var editUrl = "<?php echo site_url("admin/participants/editParticipant"); ?>";
var getSearchIDs = "<?php echo site_url("admin/participants/getSearchIDs"); ?>";
var getaddtosurveymsg = "<?php echo site_url("admin/participants/getaddtosurveymsg"); ?>";
var minusbutton = "<?php echo base_url()."images/deleteanswer.png" ?>";
var addbutton = "<?php echo base_url()."images/plus.png" ?>";
var delparticipantUrl = "<?php echo site_url("admin/participants/delParticipant");?>";
var surveylinkUrl = "<?php echo site_url("admin/participants/getSurveyInfo_json"); ?>";
var getAttribute_json = "<?php echo site_url("admin/participants/getAttribute_json");?>";
var exporttocsv = "<?php echo site_url("admin/participants/exporttocsv");?>";
var exporttocsvcount = "<?php echo site_url("admin/participants/exporttocsvcount");?>";
var getcpdbAttributes_json = "<?php echo site_url("admin/participants/exporttocsvcount");?>";
var attMapUrl = "<?php echo site_url("admin/participants/attributeMap");?>";
var editAttributevalue = "<?php echo site_url("admin/participants/editAttributevalue");?>";
var shareUrl = "<?php echo site_url("admin/participants/shareParticipants"); ?>";
var surveyUrl = "<?php echo site_url("admin/participants/addToToken"); ?>";
var postUrl = "<?php echo site_url("admin/participants/setSession"); ?>";
var ajaxUrl = "<?php echo site_url("images/ajax-loader.gif"); ?>";
var redUrl = "<?php echo site_url("admin/participants/displayParticipants");?>";
var colNames = '["participant_id","can_edit","<?php echo $clang->gT("First Name") ?>","<?php echo $clang->gT("Last Name") ?>","<?php echo $clang->gT("E-Mail") ?>","<?php echo $clang->gT("Blacklisted") ?>","<?php echo $clang->gT("Surveys") ?>","<?php echo $clang->gT("Language") ?>","<?php echo $clang->gT("Owner Name") ?>"<?php echo $columnNames; ?>]';
var colModels = '[{"name":"participant_id", "index":"participant_id", "width":100, "align":"center", "sorttype":"int", "sortable": true, "editable":false, "hidden":true},';
    colModels += '{"name":"can_edit", "index":"can_edit", "width":10, "align":"center", "sorttype":"int", "sortable": true, "editable":false, "hidden":true},';
    colModels += '{"name":"firstname", "index":"firstname", "sorttype":"string", "sortable": true, "width":120, "align":"center", "editable":true},';
    colModels += '{"name":"lastname", "index":"lastname", "sorttype":"string", "sortable": true,"width":120, "align":"center", "editable":true},';
    colModels += '{"name":"email", "index":"email","align":"center","width":300, "sorttype":"string", "sortable": true, "editable":true},';
    colModels += '{"name":"blacklisted", "index":"blacklisted","align":"center","width":80,"sorttype":"string", "sortable": true, "editable":true, "edittype":"checkbox", "editoptions":{"value":"Y:N"}},';
    colModels += '{"name":"surveys", "index":"surveys","align":"center", "sorttype":"int", "sortable": true,"width":80,"editable":false},';

<?php
    $colModels  ="colModels += '".implode(",';\n colModels += '",$langNames).",";
    $colModels .= implode(",';\n colModels += '", $uidNames)."]';";
    echo $colModels;
?>
</script>
<script src="<?php echo $this->config->item('generalscripts')."admin/displayParticipant.js" ?>" type="text/javascript"></script>
<div id ="search" style="display:none">
<?php
$optionsearch = array( '' => 'Select One',
                      'firstname' => 'First Name',
                      'lastname' => 'Last Name',
                      'email' => 'E-Mail',
                      'blacklisted' => 'Blacklisted',
                      'surveys' => 'Surveys ',
                      'language' => 'Language',
                      'owner_uid' => 'Owner ID',
                      'owner_name' => 'Owner Name');
$optioncontition = array( '' => 'Select One',
                      'equal' => 'Equals',
                      'contains' => 'Contains',
                      'notequal' => 'Not Equal',
                      'notcontains' => 'Not Contains',
                      'greaterthan' => 'Greater Than',
                      'lessthan' => 'Less Than');
if(isset($allattributes) && count($allattributes) > 0) // Add attribute names to select box
        {
        echo "<script type='text/javascript'>var optionstring = '";
           foreach($allattributes as $key=>$value)
            {
               $optionsearch[$value['attribute_id']] = $value['attribute_name'];
               echo "<option value=".$value['attribute_id'].">".$value['attribute_name']."</option>";
            }
        echo "';</script>";
        }

?>
<table id='searchtable'>
<tr>
<td><?php echo form_dropdown('field_1',$optionsearch,'','id="field_1"'); ?></td>
<td><?php echo form_dropdown('condition_1',$optioncontition,'','id="condition_1"'); ?></td>
<td><input type="text" id="conditiontext_1" style="margin-left:10px;" /></td>
<td><img src=<?php echo base_url()."images/plus.png" ?>  id="addbutton" style="margin-bottom:4px"></td>
</tr>
</table>
<br/>


</div>
<br/>
<table id="displayparticipants"></table> <div id="pager"></div>
<p><input type="button" name="share" id="share" value="Share" /><input type="button" name="addtosurvey" id="addtosurvey" value="Add to Survey" />
</p>
</table>

<div id="fieldnotselected" title="<?php echo $clang->gT("Error") ?>" style="display:none">
	<p>
		<?php echo $clang->gT("Please select a field"); ?>
	</p>
</div>
<div id="conditionnotselected" title="<?php echo $clang->gT("Error") ?>" style="display:none">
	<p>
		<?php echo $clang->gT("Please select a condition"); ?>
	</p>
</div>
<div id="norowselected" title="<?php echo $clang->gT("Error") ?>" style="display:none">
	<p>
		<?php echo $clang->gT("Please select at least one participant"); ?>
	</p>
</div>
<div id="shareform" title="<?php echo $clang->gT("Share") ?>" style="display:none">
	<p>
	<?php echo $clang->gT("User with whom the participants are to be shared"); ?></p>
        <p>
              <?php
              $options[''] = $clang->gT("---Select One---");
              foreach($names->result() as $row)
              {
                  if(!($this->session->userdata('loginID')==$row->uid))
                  {
                       $options[$row->uid] = $row->full_name;
                  }
              }
              echo form_dropdown('shareuser', $options, NULL,'id="shareuser"');
              ?>
        </p>
        <p>
        <?php echo $clang->gT("Allow this user to edit these participants"); ?>
        </p>
        <p><?php $data = array(
            'name'        => 'can_edit',
            'id'          => 'can_edit',
            'value'       => 'TRUE',
            'checked'     => TRUE,
            'style'       => 'margin:10px',
            );
        echo form_checkbox($data); ?><input type="hidden" name="can_edit" id="can_edit" value='TRUE'></p>
    </div>
        <!--<div id="addsurvey" title="addsurvey" style="display:none">-->
        <div class="ui-widget ui-helper-hidden" id="client-script-return-msg" style="display:none">
        <form action="<?php echo site_url("admin/participants/attributeMap");?>" name="addsurvey" id="addsurvey" method="POST">
            <input type="hidden" name="participant_id" id="participant_id" value=""></input>
            <input type="hidden" name="count" id="count" value=""></input>
	<p>
	<?php echo $clang->gT("Please select the survey to which participants are to be added"); ?></p>
        <p>
              <?php
                if(!empty($surveynames))
                {
                       $option[''] = $clang->gT("---Select One---");
                       foreach($surveynames as $row )
                        {
                             $option[$row['surveyls_survey_id']] = $row['surveyls_title'];
                        }
                        echo form_dropdown('survey_id',$option,NULL,'id="survey_id"');
                }

             ?>
        </p>
        <p><?php echo $clang->gT("Redirect to token table after copy "); ?>
        <?php $data = array(
            'name'        => 'redirect',
            'id'          => 'redirect',
            'value'       => 'TRUE',
            'checked'     => TRUE,
            'style'       => 'margin:10px',
            );

        echo form_checkbox($data); ?><input type="hidden" name="redirect" id="redirect" value='TRUE'></p>
        <center><ol id='selectableadd' class='selectable' >
                <li class='ui-widget-content' id='all'><?php echo $clang->gT("all participants in current search") ?></li>
                <li class='ui-widget-content' id='allingrid'><?php echo $clang->gT("all participants") ?></li>
                <li class='ui-widget-content' id='selected'><?php echo $clang->gT("only the participants I have selected") ?></li>
        </ol></center>
            </form>
</div>
<div id="notauthorised" title="notauthorised" style="display:none">
	<p>
	<?php echo $clang->gT("This is shared participant and you are not authorised to edit it"); ?></p>
    
</div>
<div id="exportcsv" title="exportcsv" style="display:none">
    <?php echo $clang->gT("Select the attribute to be exported"); ?><br/><br/>   
     <select id="attributes" name="attributes" multiple="multiple">
        <?php
            foreach($allattributes as $key=>$value)
            {
                echo "<option value=".$value['attribute_id'].">".$value['attribute_name']."</option>";
            }
        ?>
    </select>
</div>
