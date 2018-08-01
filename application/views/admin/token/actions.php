<?php
    $onclickAction = "if (confirm("
        .gT("Are you sure you want to delete the selected entries?","js")
        ."\")) {"
        .convertGETtoPOST(
            $this->createUrl("admin/tokens/sa/delete/$surveyid/", 
                [
                    "action"   => "tokens", 
                    "sid"      => $surveyid, 
                    "subaction"=>"delete",
                    "tokenids" => $id,
                    "limit"    => $limit,
                    "start"    => $start,
                    "order"    => $order
                ]
            )
        )."}";
?>

<a class="ui-icon ui-icon-pencil" onclick='<?=$onclickAction?>' title="<?php eT("Delete the selected entries");?>"></a>
<a class="ui-icon"></a>
