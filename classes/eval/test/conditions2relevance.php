<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>LimeExpressionManager:  Preview Conditions to Relevance</title>
    </head>
    <body>
        <?php
            include_once('../LimeExpressionManager.php');
            $data = LimeExpressionManager::UnitTestConvertConditionsToRelevance();
            echo count($data) . " question(s) in your database contain conditions.  Below is the mapping of question ID number to generated relevance equation<br/>";
            echo "<pre>";
            print_r($data);
            echo "</pre>";

            /* Temporary for unit testing
            $CI =& get_instance();
            $CI->load->model('question_attributes_model');
            $data = $CI->question_attributes_model->getEMRelatedRecordsForSurvey(1);
            echo "Here are the attrubutes for survey 1<br/>";
            echo $CI->db->last_query();
            echo "<pre>";
            print_r($data);
            echo "</pre>";
             */

            /* Temporary for unit testing
            $CI =& get_instance();
            $CI->load->model('answers_model');
            $data = $CI->answers_model->getAllAnswersForEM(26766);
            echo "Here are the answers for survey 26766<br/>";
            echo $CI->db->last_query();
            echo "<pre>";
            print_r($data);
            echo "</pre>";
             */
        ?>
    </body>
</html>
