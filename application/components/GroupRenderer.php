<?php


class GroupRenderer extends Renderer {

    public function render($step = null)
    {
        $step = $step || $this->session->step;
        echo "Rendering group";
        // Get the group we need to render:


        $group = $this->session->groups[$step];

        return $this->renderLayout($this->renderQuestions($group->questions));
    }

    /**
     * @param Question[] $questions
     */
    protected function renderQuestions(array $questions) {
        foreach($questions as $question) {
            /**
             *
             */
            LimeExpressionManager::singleton()->_CreateSubQLevelRelevanceAndValidationEqns();

        }
    }

    protected function renderLayout($content) {

    }
}