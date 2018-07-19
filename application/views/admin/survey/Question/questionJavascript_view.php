<?php
App()->getClientScript()->registerScript("ZOrder-EditQuestionView_question_jsviews" ,"
if(window.questionFunctions) {
    window.questionFunctions.OtherSelection('".$type."');
}
", LSYii_ClientScript::POS_POSTSCRIPT );
