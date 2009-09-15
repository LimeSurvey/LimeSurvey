<?php
// --------------------------------------------------------------------------------
// PhpConcept Library (PCL) Trace 2.0-beta1
// --------------------------------------------------------------------------------
// License GNU/GPL - Vincent Blavet - August 2003
// http://www.phpconcept.net
// --------------------------------------------------------------------------------
//
//   The PCL Trace library description is not available yet.
//   This library was first released only with PclZip library.
//   An independant release will be soon available on http://www.phpconcept.net
//
// --------------------------------------------------------------------------------
//
// Warning :
//   This library and the associated files are non commercial, non professional
//   work.
//   It should not have unexpected results. However if any damage is caused by
//   this software the author can not be responsible.
//   The use of this software is at the risk of the user.
//
// --------------------------------------------------------------------------------

  // ----- Version
  $g_pcltrace_version = "2.0-beta1";

  // ----- Internal variables
  // These values must be change by PclTrace library functions
  $g_pcl_trace_mode = "memory";
  $g_pcl_trace_filename = "trace.txt";
  $g_pcl_trace_name = array();
  $g_pcl_trace_index = 0;
  $g_pcl_trace_level = 0;
  $g_pcl_trace_suspend = false;
  //$g_pcl_trace_entries = array();


  // ----- For compatibility reason
  define ('PCLTRACE_LIB', 1);

  // --------------------------------------------------------------------------------
  // Function : TrOn($p_level, $p_mode, $p_filename)
  // Description :
  // Parameters :
  //   $p_level : Trace level
  //   $p_mode : Mode of trace displaying :
  //             'normal' : messages are displayed at function call
  //             'memory' : messages are memorized in a table and can be display by
  //                        TrDisplay() function. (default)
  //             'log'    : messages are writed in the file $p_filename
  // --------------------------------------------------------------------------------
  function PclTraceOn($p_level=1, $p_mode="memory", $p_filename="trace.txt")
  {
    TrOn($p_level, $p_mode, $p_filename);
  }
  function TrOn($p_level=1, $p_mode="memory", $p_filename="trace.txt")
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;
    global $g_pcl_trace_entries;
    global $g_pcl_trace_suspend;

    // ----- Enable trace mode
    $g_pcl_trace_level = $p_level;

    // ----- Memorize mode and filename
    switch ($p_mode) {
      case "normal" :
      case "memory" :
      case "log" :
        $g_pcl_trace_mode = $p_mode;
      break;
      default :
        $g_pcl_trace_mode = "logged";
    }

    // ----- Memorize filename
    $g_pcl_trace_filename = $p_filename;
    
    $g_pcl_trace_suspend = false;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : IsTrOn()
  // Description :
  // Return value :
  //   The trace level (0 for disable).
  // --------------------------------------------------------------------------------
  function PclTraceIsOn()
  {
    return IsTrOn();
  }
  function IsTrOn()
  {
    global $g_pcl_trace_level;

    return($g_pcl_trace_level);
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : TrOff()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclTraceOff()
  {
    TrOff();
  }
  function TrOff()
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;

    // ----- Clean
    $g_pcl_trace_mode = "memory";
    unset($g_pcl_trace_entries);
    unset($g_pcl_trace_name);
    unset($g_pcl_trace_index);

    // ----- Switch off trace
    $g_pcl_trace_level = 0;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclTraceSuspend()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclTraceSuspend()
  {
    global $g_pcl_trace_suspend;


    $g_pcl_trace_suspend = true;
  }
  // --------------------------------------------------------------------------------


  // --------------------------------------------------------------------------------
  // Function : PclTraceResume()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclTraceResume()
  {
    global $g_pcl_trace_suspend;


    $g_pcl_trace_suspend = false;
  }
  // --------------------------------------------------------------------------------


  // --------------------------------------------------------------------------------
  // Function : TrFctStart()
  // Description :
  //   Just a trace function for debbugging purpose before I use a better tool !!!!
  //   Start and stop of this function is by $g_pcl_trace_level global variable.
  // Parameters :
  //   $p_level : Level of trace required.
  // --------------------------------------------------------------------------------
  function PclTraceFctStart($p_file, $p_line, $p_name, $p_param="", $p_message="")
  {
    TrFctStart($p_file, $p_line, $p_name, $p_param, $p_message);
  }
  function TrFctStart($p_file, $p_line, $p_name, $p_param="", $p_message="")
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;
    global $g_pcl_trace_entries;
    global $g_pcl_trace_suspend;

    // ----- Look for disabled trace
    if (($g_pcl_trace_level < 1) || ($g_pcl_trace_suspend))
      return;

    // ----- Add the function name in the list
    if (!isset($g_pcl_trace_name))
      $g_pcl_trace_name = $p_name;
    else
      $g_pcl_trace_name .= ",".$p_name;

    // ----- Update the function entry
    $i = sizeof($g_pcl_trace_entries);
    $g_pcl_trace_entries[$i]['name'] = $p_name;
    $g_pcl_trace_entries[$i]['param'] = $p_param;
    $g_pcl_trace_entries[$i]['message'] = "";
    $g_pcl_trace_entries[$i]['file'] = $p_file;
    $g_pcl_trace_entries[$i]['line'] = $p_line;
    $g_pcl_trace_entries[$i]['index'] = $g_pcl_trace_index;
    $g_pcl_trace_entries[$i]['type'] = "1"; // means start of function

    // ----- Update the message entry
    if ($p_message != "")
    {
    $i = sizeof($g_pcl_trace_entries);
    $g_pcl_trace_entries[$i]['name'] = "";
    $g_pcl_trace_entries[$i]['param'] = "";
    $g_pcl_trace_entries[$i]['message'] = $p_message;
    $g_pcl_trace_entries[$i]['file'] = $p_file;
    $g_pcl_trace_entries[$i]['line'] = $p_line;
    $g_pcl_trace_entries[$i]['index'] = $g_pcl_trace_index;
    $g_pcl_trace_entries[$i]['type'] = "3"; // means message
    }

    // ----- Action depending on mode
    PclTraceAction($g_pcl_trace_entries[$i]);

    // ----- Increment the index
    $g_pcl_trace_index++;
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : TrFctEnd()
  // Description :
  //   Just a trace function for debbugging purpose before I use a better tool !!!!
  //   Start and stop of this function is by $g_pcl_trace_level global variable.
  // Parameters :
  //   $p_level : Level of trace required.
  // --------------------------------------------------------------------------------
  function PclTraceFctEnd($p_file, $p_line, $p_return=1, $p_message="")
  {
    TrFctEnd($p_file, $p_line, $p_return, $p_message);
  }
  function TrFctEnd($p_file, $p_line, $p_return=1, $p_message="")
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;
    global $g_pcl_trace_entries;
    global $g_pcl_trace_suspend;

    // ----- Look for disabled trace
    if (($g_pcl_trace_level < 1) || ($g_pcl_trace_suspend))
      return;

    // ----- Extract the function name in the list
    // ----- Remove the function name in the list
    if (!($v_name = strrchr($g_pcl_trace_name, ",")))
    {
      $v_name = $g_pcl_trace_name;
      $g_pcl_trace_name = "";
    }
    else
    {
      $g_pcl_trace_name = substr($g_pcl_trace_name, 0, strlen($g_pcl_trace_name)-strlen($v_name));
      $v_name = substr($v_name, -strlen($v_name)+1);
    }

    // ----- Decrement the index
    $g_pcl_trace_index--;

    // ----- Update the message entry
    if ($p_message != "")
    {
    $i = sizeof($g_pcl_trace_entries);
    $g_pcl_trace_entries[$i]['name'] = "";
    $g_pcl_trace_entries[$i]['param'] = "";
    $g_pcl_trace_entries[$i]['message'] = $p_message;
    $g_pcl_trace_entries[$i]['file'] = $p_file;
    $g_pcl_trace_entries[$i]['line'] = $p_line;
    $g_pcl_trace_entries[$i]['index'] = $g_pcl_trace_index;
    $g_pcl_trace_entries[$i]['type'] = "3"; // means message
    }

    // ----- Update the function entry
    $i = sizeof($g_pcl_trace_entries);
    $g_pcl_trace_entries[$i]['name'] = $v_name;
    $g_pcl_trace_entries[$i]['param'] = $p_return;
    $g_pcl_trace_entries[$i]['message'] = "";
    $g_pcl_trace_entries[$i]['file'] = $p_file;
    $g_pcl_trace_entries[$i]['line'] = $p_line;
    $g_pcl_trace_entries[$i]['index'] = $g_pcl_trace_index;
    $g_pcl_trace_entries[$i]['type'] = "2"; // means end of function

    // ----- Action depending on mode
    PclTraceAction($g_pcl_trace_entries[$i]);
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : TrFctMessage()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclTraceFctMessage($p_file, $p_line, $p_level, $p_message="")
  {
    TrFctMessage($p_file, $p_line, $p_level, $p_message);
  }
  function TrFctMessage($p_file, $p_line, $p_level, $p_message="")
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;
    global $g_pcl_trace_entries;
    global $g_pcl_trace_suspend;

    // ----- Look for disabled trace
    if (($g_pcl_trace_level < $p_level) || ($g_pcl_trace_suspend))
      return;

    // ----- Update the entry
    $i = sizeof($g_pcl_trace_entries);
    $g_pcl_trace_entries[$i]['name'] = "";
    $g_pcl_trace_entries[$i]['param'] = "";
    $g_pcl_trace_entries[$i]['message'] = $p_message;
    $g_pcl_trace_entries[$i]['file'] = $p_file;
    $g_pcl_trace_entries[$i]['line'] = $p_line;
    $g_pcl_trace_entries[$i]['index'] = $g_pcl_trace_index;
    $g_pcl_trace_entries[$i]['type'] = "3"; // means message of function

    // ----- Action depending on mode
    PclTraceAction($g_pcl_trace_entries[$i]);
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : TrMessage()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclTraceMessage($p_file, $p_line, $p_level, $p_message="")
  {
    TrMessage($p_file, $p_line, $p_level, $p_message);
  }
  function TrMessage($p_file, $p_line, $p_level, $p_message="")
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;
    global $g_pcl_trace_entries;
    global $g_pcl_trace_suspend;

    // ----- Look for disabled trace
    if (($g_pcl_trace_level < $p_level) || ($g_pcl_trace_suspend))
      return;

    // ----- Update the entry
    $i = sizeof($g_pcl_trace_entries);
    $g_pcl_trace_entries[$i]['name'] = "";
    $g_pcl_trace_entries[$i]['param'] = "";
    $g_pcl_trace_entries[$i]['message'] = $p_message;
    $g_pcl_trace_entries[$i]['file'] = $p_file;
    $g_pcl_trace_entries[$i]['line'] = $p_line;
    $g_pcl_trace_entries[$i]['index'] = $g_pcl_trace_index;
    $g_pcl_trace_entries[$i]['type'] = "4"; // means simple message

    // ----- Action depending on mode
    PclTraceAction($g_pcl_trace_entries[$i]);
  }
  // --------------------------------------------------------------------------------


  // --------------------------------------------------------------------------------
  // Function : TrDisplay()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclTraceDisplay()
  {
    TrDisplay();
  }
  function TrDisplay()
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;
    global $g_pcl_trace_entries;
    global $g_pcl_trace_suspend;

    // ----- Look for disabled trace
    if (($g_pcl_trace_level <= 0) || ($g_pcl_trace_mode != "memory") || ($g_pcl_trace_suspend))
      return;

    $v_font = "\"Verdana, Arial, Helvetica, sans-serif\"";

    // ----- Trace Header
    echo "<table width=100% border=0 cellspacing=0 cellpadding=0>";
    echo "<tr bgcolor=#0000CC>";
    echo "<td bgcolor=#0000CC width=1>";
    echo "</td>";
    echo "<td><div align=center><font size=3 color=#FFFFFF face=$v_font>Trace</font></div></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td bgcolor=#0000CC width=1>";
    echo "</td>";
    echo "<td>";

    // ----- Content header
    echo "<table width=100% border=0 cellspacing=0 cellpadding=0>";

    // ----- Display
    $v_again=0;
    for ($i=0; $i<sizeof($g_pcl_trace_entries); $i++)
    {
      // ---- Row header
      echo "<tr>";
      echo "<td><table width=100% border=0 cellspacing=0 cellpadding=0><tr>";
      $n = ($g_pcl_trace_entries[$i]['index']+1)*10;
      echo "<td width=".$n."><table width=100% border=0 cellspacing=0 cellpadding=0><tr>";

      for ($j=0; $j<=$g_pcl_trace_entries[$i]['index']; $j++)
      {
        if ($j==$g_pcl_trace_entries[$i]['index'])
        {
          if (($g_pcl_trace_entries[$i]['type'] == 1) || ($g_pcl_trace_entries[$i]['type'] == 2))
          echo "<td width=10><div align=center><font size=2 face=$v_font>+</font></div></td>";
        }
        else
          echo "<td width=10><div align=center><font size=2 face=$v_font>|</font></div></td>";
      }
      //echo "<td>&nbsp</td>";
      echo "</tr></table></td>";

      echo "<td width=2></td>";
      switch ($g_pcl_trace_entries[$i]['type']) {
        case 1:
          echo "<td><font size=2 face=$v_font>".$g_pcl_trace_entries[$i]['name']."(".$g_pcl_trace_entries[$i]['param'].")</font></td>";
        break;
        case 2:
          echo "<td><font size=2 face=$v_font>".$g_pcl_trace_entries[$i]['name']."()=".$g_pcl_trace_entries[$i]['param']."</font></td>";
        break;
        case 3:
        case 4:
          echo "<td><table width=100% border=0 cellspacing=0 cellpadding=0><td width=20></td><td>";
          echo "<font size=2 face=$v_font>".$g_pcl_trace_entries[$i]['message']."</font>";
          echo "</td></table></td>";
        break;
        default:
        echo "<td><font size=2 face=$v_font>".$g_pcl_trace_entries[$i]['name']."(".$g_pcl_trace_entries[$i]['param'].")</font></td>";
      }
      echo "</tr></table></td>";
      echo "<td width=5></td>";
      echo "<td><font size=1 face=$v_font>".basename($g_pcl_trace_entries[$i]['file'])."</font></td>";
      echo "<td width=5></td>";
      echo "<td><font size=1 face=$v_font>".$g_pcl_trace_entries[$i]['line']."</font></td>";
      echo "</tr>";
    }

    // ----- Content footer
    echo "</table>";

    // ----- Trace footer
    echo "</td>";
    echo "<td bgcolor=#0000CC width=1>";
    echo "</td>";
    echo "</tr>";
    echo "<tr bgcolor=#0000CC>";
    echo "<td bgcolor=#0000CC width=1>";
    echo "</td>";
    echo "<td><div align=center><font color=#FFFFFF face=$v_font>&nbsp</font></div></td>";
    echo "</tr>";
    echo "</table>";
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : TrDisplayNew()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclTraceDisplayNew()
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;
    global $g_pcl_trace_entries;
    global $g_pcl_trace_suspend;

    // ----- Look for disabled trace
    if (($g_pcl_trace_level <= 0) || ($g_pcl_trace_mode != "memory") || ($g_pcl_trace_suspend))
      return;

?>

<script language="javascript">
function PclTraceToggleView(element) {
	if (element.style.visibility == 'visible') {
	    PclTraceHide(element);
	} else {
	    PclTraceShow(element);
	}
}
function PclTraceShow(element) {
	    element.style.visibility = 'visible';
	    element.style.position='relative';
}
function PclTraceHide(element) {
	    element.style.visibility = 'hidden';
	    element.style.position='absolute';
}

</script>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bordercolor="#0000CC">
  <tr>
    <td bgcolor="#0000CC">
      <div align="center"><font face="Verdana, Arial, Helvetica, sans-serif" color="#FFFFFF"><b>Trace</b></font></div>
    </td>
  </tr>
  <tr>
    <td>
<?php
    $v_font = "\"Verdana, Arial, Helvetica, sans-serif\"";

    // ----- Trace Header

    // ----- Display the items
    $v_again=0;
    for ($i=0; $i<sizeof($g_pcl_trace_entries); $i++)
    {
      switch ($g_pcl_trace_entries[$i]['type']) {
        case 1: // fct start
		  PclTraceDisplayItemStart($i);
        break;
        case 2: // fct stop
		  PclTraceDisplayItemStop($i);
        break;
        case 3: // fct msg
        case 4: // msg
          PclTraceDisplayItemMsg($i);
        break;
        default:
      }
/*
      echo "</tr></table></td>";
      echo "<td width=5></td>";
      echo "<td><font size=1 face=$v_font>".basename($g_pcl_trace_entries[$i]['file'])."</font></td>";
      echo "<td width=5></td>";
      echo "<td><font size=1 face=$v_font>".$g_pcl_trace_entries[$i]['line']."</font></td>";
      echo "</tr>";
      */
    }

    // ----- Trace footer
?>
    </td>
  </tr>
  <tr>
    <td bgcolor="#0000CC">&nbsp;</td>
  </tr>
</table>

<script language="javascript">
function PclTraceShowAll() {
<?php
    for ($i=0; $i<sizeof($g_pcl_trace_entries); $i++) {
      if ($g_pcl_trace_entries[$i]['type'] == 1) {
        echo "PclTraceShow(document.getElementById('fct-".$i."'));";
      }
    }
?>
}
function PclTraceHideAll() {
<?php
    for ($i=0; $i<sizeof($g_pcl_trace_entries); $i++) {
      if ($g_pcl_trace_entries[$i]['type'] == 1) {
        echo "PclTraceHide(document.getElementById('fct-".$i."'));";
      }
    }
?>
}

</script>
<form id="formulaire" action="POST">
<p>
<input type='button' value='Show All' onclick="PclTraceShowAll();"></input>
<input type='button' value='Hide All' onclick="PclTraceHideAll();"></input>
</p>
</form>


<?php
  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : TrDisplayNew()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclTraceDisplayItemStart($p_id)
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;
    global $g_pcl_trace_entries;
    global $g_pcl_trace_suspend;

?>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="10"><font face="Verdana, Arial, Helvetica, sans-serif" color="#FFFFFF"><b><font color="#000000" size="2">+</font></b></font></td>
          <td style="width:2px;"></td>
          <td><font face="Verdana, Arial, Helvetica, sans-serif" color="#FFFFFF"><b><font color="#000000" size="2">
		      <a href="javascript:null();"
				 title="<?php echo 'File:'.basename($g_pcl_trace_entries[$p_id]['file'])." Line: ".$g_pcl_trace_entries[$p_id]['line'];?>"
			     onClick="PclTraceToggleView(document.getElementById('<?php echo 'fct-'.$p_id; ?>'));">
			  <?php echo $g_pcl_trace_entries[$p_id]['name']."(".$g_pcl_trace_entries[$p_id]['param'].")" ?>
			  </a></font></b></font></td>
        </tr>
        <tr id="<?php echo 'fct-'.$p_id; ?>" style="visibility:hidden;position:absolute;">
          <td width="10">&nbsp;</td>
          <td style="width:2px;" bgcolor="#0000CC"></td>
          <td>
<?php

  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : TrDisplayNew()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclTraceDisplayItemStop($p_id)
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;
    global $g_pcl_trace_entries;
    global $g_pcl_trace_suspend;

?>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td><font face="Verdana, Arial, Helvetica, sans-serif" color="#FFFFFF"><b><font color="#000000" size="2">
			  <?php echo $g_pcl_trace_entries[$p_id]['name']."()=".$g_pcl_trace_entries[$p_id]['param']; ?>
			  </font></b></font></td>
        </tr>
      </table>

          </td>
        </tr>
      </table>

<?php


  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : TrDisplayNew()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclTraceDisplayItemMsg($p_id)
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;
    global $g_pcl_trace_entries;
    global $g_pcl_trace_suspend;

?>
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td width="10"><font face="Verdana, Arial, Helvetica, sans-serif" color="#FFFFFF"><b><font color="#000000" size="2"><center>.</center></font></b></font></td>
          <td style="width:2px;"></td>
          <td><font face="Verdana, Arial, Helvetica, sans-serif" color="#FFFFFF"><b><font color="#000000" size="2">
		      
			  <?php echo $g_pcl_trace_entries[$p_id]['message'] ?>
			  </font></b></font></td>


      <td width=5></td>
      <td><font size=1 face="Verdana, Arial, Helvetica, sans-serif"><?php echo basename($g_pcl_trace_entries[$p_id]['file']); ?></font></td>
      <td width=5></td>
      <td><font size=1 face="Verdana, Arial, Helvetica, sans-serif"><?php echo $g_pcl_trace_entries[$p_id]['line']; ?></font></td>
        </tr>
      </table>

<?php

  }
  // --------------------------------------------------------------------------------

  // --------------------------------------------------------------------------------
  // Function : PclTraceAction()
  // Description :
  // Parameters :
  // --------------------------------------------------------------------------------
  function PclTraceAction($p_entry)
  {
    global $g_pcl_trace_level;
    global $g_pcl_trace_mode;
    global $g_pcl_trace_filename;
    global $g_pcl_trace_name;
    global $g_pcl_trace_index;
    global $g_pcl_trace_entries;

    if ($g_pcl_trace_mode == "normal")
    {
      for ($i=0; $i<$p_entry['index']; $i++)
        echo "---";
      if ($p_entry['type'] == 1)
        echo "<b>".$p_entry['name']."</b>(".$p_entry['param'].") : ".$p_entry['message']." [".$p_entry[file].", ".$p_entry[line]."]<br>";
      else if ($p_entry['type'] == 2)
        echo "<b>".$p_entry['name']."</b>()=".$p_entry['param']." : ".$p_entry['message']." [".$p_entry[file].", ".$p_entry[line]."]<br>";
      else
        echo $p_entry['message']." [".$p_entry['file'].", ".$p_entry['line']."]<br>";
    }
  }
  // --------------------------------------------------------------------------------

?>
