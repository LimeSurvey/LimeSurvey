<?php
    $currentfieldset='';
    foreach ($attributedata as $index=>$aAttribute)
    {
        if ($currentfieldset!=$aAttribute['category'])
        {
            if ($currentfieldset!='')
            {?>
            </fieldset><?php
            }
            $currentfieldset=$aAttribute['category'];
        ?>
        <fieldset>
        <legend><?php echo $aAttribute['category'];?></legend>
            <?php
        }?>
            <div class="form-group">
                <label class="col-sm-4 control-label" for='<?php echo $aAttribute['name'];?>' title='<?php echo $aAttribute['help'];?>'><?php echo $aAttribute['caption'];
                        if ($aAttribute['i18n']==true) { ?> (<?php echo $aAttribute['language'] ?>)<?php }?>:</label>
                <div class="col-sm-8">
                <?php
                    if ($aAttribute['readonly'] && $bIsActive)
                    {
                        echo $aAttribute['value'];
                    }
                    else
                    {
                        switch ($aAttribute['inputtype']){
                            case 'singleselect':    echo "<select class='form-control' id='{$aAttribute['name']}' name='{$aAttribute['name']}'>";
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
                            case 'text':?> <input type='text' class="form-control" id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>' value='<?php echo htmlspecialchars($aAttribute['value'],ENT_QUOTES, 'UTF-8');?>' />
                            <?php
                                break;
                            case 'integer':?> <input type='text' class="form-control" id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>' value='<?php echo $aAttribute['value'];?>' />
                            <?php
                                break;
                            case 'textarea':?> <textarea class="form-control" id='<?php echo $aAttribute['name'];?>' name='<?php echo $aAttribute['name'];?>'><?php echo $aAttribute['value'];?></textarea>
                            <?php
                                break;
                        }
                }?>
                </div>
            </div>
        <?php }?>
