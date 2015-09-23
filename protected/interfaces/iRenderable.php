<?php
namespace ls\interfaces;

interface iRenderable {
    /**
     * This function renders the object.
     * It MUST NOT produce any output.
     * It should return a string or an object that can be converted to string.
     * @param iResponse $response
     * @param \SurveySession $session
     * @return string
     */
    public function render(iResponse $response, \SurveySession $session);
}