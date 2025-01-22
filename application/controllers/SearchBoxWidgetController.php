<?php

use LimeSurvey\Api\Rest\Endpoint\EndpointFactory;
use LimeSurvey\DI;

// Ensure the Controller class is imported
Yii::import('application.components.Controller');
// Ensure the SearchBoxWidget class is imported
Yii::import('application.extensions.admin.SearchBoxWidget.SearchBoxWidget');

class SearchBoxWidgetController extends LSBaseController
{
    public function actionGetSurveyResponseTrends()
{
    // Fetch the surveyid from GET parameters
    $surveyId = Yii::app()->request->getParam('surveyid', null);  // Using getParam() for query string
    Yii::log('Survey ID in widget: ' . $surveyId, 'info'); // Debugging

    if ($surveyId) {
        // Call the method in the widget class to fetch survey response trends
        $widget = new SearchBoxWidget();
        $data = $widget->getSurveyResponseTrends($surveyId);  // Call the method from the widget class
        echo json_encode($data);
    } else {
        Yii::log('No survey ID received', 'error'); // Error Logging
        echo json_encode(['error' => 'No survey ID provided']);
    }
    Yii::app()->end();
}
    
    
}
