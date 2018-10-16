<?php
/**
 * This file is part of reloadAnyResponse plugin
 */
namespace demoAddEmFunction;
class exampleFunctions
{
    public static function sayHello($message)
    {
        return "Hello ".$message;
    }
    public static function doHtmlList($elements)
    {
        if(!count($elements)) {
            return "";
        }
        $returnHtml = "";
        foreach($elements as $element) {
            if(strval($element)) {
                $returnHtml .= "<li>".$element."</li>";
            }
        }
        if($returnHtml) {
            $returnHtml = "<ul>".$returnHtml."</ul>";
        }
        return $returnHtml;
    }
}
