<?php


namespace ls\controllers\surveys;


class Script extends \Action
{
    /**
     * Renders the relevance and tailoring javascript for a survey.
     */
    public function run($id)
    {
        $survey = $this->loadModel($id);

        $result = \LimeExpressionManager::getScript($survey);
        header('Content-type: application/javascript');
        header('Cache-control: public, max-age=7200');
        header_remove('Pragma');
        header_remove('Expires');
        foreach (App()->log->routes as $route) {
            if ($route instanceof \CWebLogRoute) {
                $route->enabled = false;
            }
        }
        echo $result;
    }
}