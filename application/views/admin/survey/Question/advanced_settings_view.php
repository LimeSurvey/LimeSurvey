<?php
    $currentfieldset='';
    foreach ($attributedata as $aAttribute)
    {
        if ($currentfieldset!=$aAttribute['category'])
        {
            if ($currentfieldset!='')
            {?>
                </ul></fieldset><?php
            }
            $currentfieldset=$aAttribute['category'];
            ?>
            <fieldset>
                <legend><?php echo $aAttribute['category'];?></legend>
                <ul>
        <?php
        }?>
    <li>
        <label for='<?php echo $aAttribute['name'];?>' title='<?php echo $aAttribute['help'];?>'><?php echo $aAttribute['caption'];
            if ($aAttribute['i18n']==true) { ?> (<?php echo $aAttribute['language'] ?>)<?php }?>
        </label>
        <?php
            if ($aAttribute['readonly'])
            {
                echo $aAttribute['value'];
            }
            else
            {
                switch ($aAttribute['inputtype']){
                    case 'singleselect':    echo "<select id='{$aAttribute['name']}' name='{$aAttribute['name']}'>";
                        foreach($aAttribute['options'] as $sOptionvalue=>$sOptiontext)
                        {
                            echo "<option value='{$sOptionvalue}' ";
                            if ($aAttribute['value']==$sOptionvalue)
                            {
                                echo " selected='selected' ";
                            }
                            echo ">{$sOptiontext}</option>";
                        }
                    echo "</select>";
                    break;
                    case 'text':?> <input type='text' id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>' value='<?php echo $aAttribute['value'];?>' />
                    <?php
                    break;
                    case 'integer':?> <input type='text' id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>' value='<?php echo $aAttribute['value'];?>' />
                    <?php
                    break;
                    case 'textarea':?> <textarea id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>'><?php echo $aAttribute['value'];?></textarea>
                    <?php
                    break;
                }
            }?>
            </li>
<?php }?>
    </ul></fieldset>
