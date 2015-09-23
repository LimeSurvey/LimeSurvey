<?php

/**
 * Class Action
 * This extends Yii's default action class with some function wrappers to access controller functions.
 * @property string $layout
 * @property \ls\controllers\Controller $controller
 */
class Action extends \CAction
{

    /**
     * Renders a view with a layout.
     *
     * This method first calls {@link renderPartial} to render the view (called content view).
     * It then renders the layout view which may embed the content view at appropriate place.
     * In the layout view, the content view rendering result can be accessed via variable
     * <code>$content</code>. At the end, it calls {@link processOutput} to insert scripts
     * and dynamic contents if they are available.
     *
     * By default, the layout view script is "protected/views/layouts/main.php".
     * This may be customized by changing {@link layout}.
     *
     * @param string $view name of the view to be rendered. See {@link getViewFile} for details
     * about how the view script is resolved.
     * @param array $data data to be extracted into PHP variables and made available to the view script
     * @param boolean $return whether the rendering result should be returned instead of being displayed to end users.
     * @return string the rendering result. Null if the rendering result is not required.
     * @see renderPartial
     * @see getLayoutFile
     */
    public function render($view, $data = null, $return = false) {
        return $this->controller->render($view, $data, $return);
    }

    public function setLayout($value) {
        $this->controller->layout = $value;
    }

    public function getLayout($value) {
        return $this->controller->layout;
    }

    public function loadModel($id) {
        return $this->controller->loadModel($id);
    }
}