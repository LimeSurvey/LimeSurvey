<?php
/* @var $this AdminController */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('expressionsFunctions');
$aFunctions = ExpressionManager::GetAllowableFunctions();

?>
<div class="container container-center">
    <div class="row">
        <div class="col-sm-12">
            <h3>Functions available within Expression Manager</h3>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <table border='1' class='table' id="selector__EmAvailFunctions">
                <thead>
                    <tr>
                        <th>Function</th>
                        <th>Meaning</th>
                        <th>Syntax</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aFunctions as $sName => $aFuncDefinition) { ?>
                        <tr>
                            <td>
                                <?=$sName?>
                            </td>
                            <td>
                                <?=$aFuncDefinition[2]?>
                            </td>
                            <td>
                                <?=$aFuncDefinition[3]?>
                            </td>
                            <td>
                                <?=(!empty($aFuncDefinition[4]) ? '<a href="'.$aFuncDefinition[4].'">'.$aFuncDefinition[4].'</a>' : "&nbsp;")?>
                            </td>
                        </tr>
                    <?php } ?> 
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
    App()->getClientScript()->registerPackage('jquery-datatable');
    App()->getClientScript()->registerScript("EMFunctionDefinitionDataTable", "
        $('#selector__EmAvailFunctions').DataTable({
            pageLength: 10
          });
    ", LSYii_ClientScript::POS_POSTSCRIPT);
?>
