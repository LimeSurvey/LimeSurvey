<?php

/** @var $model array*/
/** @var $survey Survey[] */

echo '<h3> List of Surveys </h3>';


foreach ($survey as $surv){
    /**@var $surv Survey */
    echo 'admin: ' . $surv->admin  . 'surveyname_id: ' . $surv->sid . '<br>';
}


