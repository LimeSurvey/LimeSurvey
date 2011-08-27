<div id='tokens'><ul>
        
         <li><label for=''><?php echo $clang->gT("Enable token-based response persistence?"); ?></label>
         <select id='tokenanswerspersistence' name='tokenanswerspersistence' onchange="javascript: if (document.getElementById('anonymized').value == 'Y') {alert('<?php echo $clang->gT("This option can't be set if the `Anonymized responses` option is active.","js"); ?>'); this.value='N';}">
         <option value='Y'
        <?php if ($esrow['tokenanswerspersistence'] == "Y") { ?>
              selected='selected'
        <?php } ?>
         ><?php echo $clang->gT("Yes"); ?></option>
         <option value='N'
        <?php if ($esrow['tokenanswerspersistence'] == "N") { ?>
              selected='selected'
        <?php } ?>
         ><?php echo $clang->gT("No"); ?></option>
        </select></li>

        
         <li><label for='tokenlength'><?php echo $clang->gT("Set token length to:"); ?></label>
         <input type='text' value="<?php echo $esrow['tokenlength']; ?>" name='tokenlength' id='tokenlength' size='12' maxlength='2' onkeypress="return goodchars(event,'0123456789')" />
         </li>

        
         </ul></div>