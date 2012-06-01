<?php
abstract class TextQuestion extends QuestionModule
{
    public function getPopup($notanswered=null)
    {
        global $showpopups;

        $clang = Yii::app()->lang;

        if (is_array($notanswered) && isset($showpopups) && $showpopups == 1) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
        {
            global $mandatorypopup, $popup;
            //POPUP WARNING
            if (!isset($mandatorypopup))
            {
                return $popup="<script type=\"text/javascript\">\n
                <!--\n $(document).ready(function(){
                alert(\"".$clang->gT("You cannot proceed until you enter some text for one or more questions.", "js")."\");});\n //-->\n
                </script>\n";
            }else
            {
                return $popup="<script type=\"text/javascript\">\n
                <!--\n $(document).ready(function(){
                alert(\"".$clang->gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.", "js")."\");});\n //-->\n
                </script>\n";
            }
        }
        return false;
    }
}
?>