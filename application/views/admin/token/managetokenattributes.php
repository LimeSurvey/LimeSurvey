    <div class='header ui-widget-header'><?php echo $clang->gT("Manage token attribute fields");?></div>
 
    <form action="<?php echo $this->createUrl("admin/tokens/sa/updatetokenattributedescriptions/surveyid/$surveyid");?>" method="post">'
    <table class='listsurveys'><tr><th><?php echo $clang->gT("Attribute field");?></th><th><?php echo $clang->gT("Field description");?></th><th><?php echo $clang->gT("Example data");?></th></tr>


<?php
    foreach ($tokenfields as $tokenfield=>$tokendescription)
    {
        $nrofattributes++;
        echo "<tr><td>$tokenfield</td><td><input type='text' name='description_$tokenfield' value='".htmlspecialchars($tokendescription,ENT_QUOTES,'UTF-8')."' /></td><td>";
        if ($examplerow!==false)
        {
            if(!$tokenfield[10]=='c')
            {
            echo htmlspecialchars($examplerow[$tokenfield]);
            }
        }
        else
        {
            echo $clang->gT('<no data>');
        }
        echo "</td></tr>";
    } ?>
    </table><p>
    	
    <input type="submit" value="<?php echo $clang->gT('Save');?>" />
    <input type='hidden' name='action' value='tokens' />
    <input type='hidden' name='subaction' value='updatetokenattributedescriptions' />
    <input type='hidden' name='sid' value="<?php echo $surveyid;?>" /></p>
    </form><br /><br />

    <div class='header ui-widget-header'><?php echo $clang->gT("Add token attributes");?></div><p>

    <?php echo sprintf($clang->gT('There are %s user attribute fields in this token table'),$nrofattributes);?></p>
    <form id="addattribute" action="<?php echo $this->createUrl("admin/tokens/sa/updatetokenattributes/surveyid/$surveyid");?>" method="post">
    <p>
    <label for="addnumber"><?php echo $clang->gT('Number of attribute fields to add:');?></label>
    <input type="text" id="addnumber" name="addnumber" size="3" maxlength="3" value="1" />
    </p>
    <p>
    <input type="submit" value="<?php echo $clang->gT('Add fields');?>" />
    <input type='hidden' name='action' value='tokens' />
    <input type='hidden' name='subaction' value='updatetokenattributes' />
    <input type='hidden' name='sid' value="<?php echo $surveyid;?>" />
    </p>
    </form>
    <br /><br />
