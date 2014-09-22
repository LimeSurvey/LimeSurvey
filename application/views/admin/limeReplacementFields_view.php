<script language="javascript">

    $(document).ready(function ()
    {
        LoadSelected() ;
        mydialog.SetOkButton( true ) ;
        SelectField( 'cquestions' ) ;
    });

    var eSelected = dialog.Selection.GetSelectedElement() ;

    function LoadSelected()
    {
        if ( !eSelected )
            return ;
        if ( eSelected.tagName == 'SPAN' && eSelected._fckLimeReplacementFields )
            document.getElementById('cquestions').value = eSelected._fckLimeReplacementFields ;
        else
            eSelected == null ;
    }

    function Ok()
    {
        var sValue = document.getElementById('cquestions').value ;

        FCKLimeReplacementFieldss.Add( sValue ) ;
        return true ;
    }
</script>
</head>
<body scroll="no" style="OVERFLOW: hidden;">
<table height="100%" cellSpacing="0" cellPadding="0" width="100%" border="0">
    <tr>
        <td>
        <?php
            if (count($replFields) > 0 || isset($cquestions) )
            {
            $InsertansUnsupportedtypes= Yii::app()->getConfig('InsertansUnsupportedtypes');
            ?>
            <select name='cquestions' id='cquestions' size='14' ondblclick="$('.cke_dialog_ui_button_ok').children().click();">
                <?php
                    $noselection = false;
                }
                else
                {
                    $clang->eT("No replacement variable available for this field");
                    $noselection = true;
                }

                if (count($replFields) > 0)
                {
                ?>
                <optgroup label='<?php $clang->eT("Standard Fields");?>'>
                    <?php

                        foreach ($replFields as $stdfield)
                        {
                        ?>
                        <option value='<?php echo $stdfield[0];?>' title='<?php echo $stdfield[1];?>'><?php echo $stdfield[1];?></option>
                        <?php
                        }
                    ?>
                </optgroup>
                <?php
                }

                if (isset($cquestions))
                {
                ?>
                <optgroup label='<?php $clang->eT("Previous answer fields");?>'>
                    <?php
                        foreach ($cquestions as $cqn)
                        {
                            $isDisabled="";
                            if (in_array($cqn[2],$InsertansUnsupportedtypes))
                            {
                                $isDisabled=" disabled='disabled'";
                            }
                            elseif ($cqn[4] === false)
                            {
                                $isDisabled=" disabled='disabled'";
                            }
                        ?>
                        <option value='INSERTANS:<?php echo $cqn[3];?>' title='<?php echo $cqn[0];?>' <?php echo $isDisabled;?>><?php echo $cqn[0];?></option>
                        <?php
                        }
                    ?>
                </optgroup>
                <?php
                }

                if ($noselection === false)
                {
                ?>
            </select>
            <?php
            }
        ?>
        </td>
    </tr>
    <?php
        if (isset($surveyformat))
        {
            switch ($surveyformat)
            {
                case 'A':
                ?>
                <tr>
                    <td>
                        <br />
                        <font color='orange'><?php $clang->eT("Some Question have been disabled");?></font>
                        <br />
                        <?php echo sprintf($clang->gT("Survey Format is %s:"), $clang->gT("All in one"));?>
                        <br />
                        <i><?php $clang->eT("Only Previous pages answers are available");?></i>
                        <br />
                    </td>
                </tr>
                <?php
                    break;
                case 'G':
                ?>
                <tr>
                    <td>
                        <br />
                        <font color='orange'><?php $clang->eT("Some Question have been disabled");?></font>
                        <br /><?php echo sprintf($clang->gT("Survey mode is set to %s:"), $clang->gT("Group by Group"));?>
                        <br/><i><?php $clang->eT("Only Previous pages answers are available");?>
                        </i><br />
                    </td></tr>
                <?php
                    break;
            }
        }
    ?>
</table>
