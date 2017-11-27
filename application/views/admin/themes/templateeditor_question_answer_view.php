<!-- templateeditor_question_answer_view -->
<?php
if (isset($alt)){
    App()->getController()->renderPartial('/admin/themes/templateeditor_question_answertext_view');
}else{
    App()->getController()->renderPartial('/admin/themes/templateeditor_question_answerlist_view');
}
?>
<!-- endof templateeditor_question_answer_view -->
