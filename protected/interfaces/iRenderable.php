<?php
namespace ls\interfaces;

interface iRenderable {
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param ResponseInterface $response
     * @param \ls\components\SurveySession $session
     * @return string
     */
    public function render(ResponseInterface $response, \ls\components\SurveySession $session);
}