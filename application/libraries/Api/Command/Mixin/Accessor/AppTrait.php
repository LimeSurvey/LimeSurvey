<?php

namespace LimeSurvey\Api\Command\Mixin\Accessor;

use Yii;

trait AppTrait
{
    private $app = null;

    /**
     * Get app
     *
     * Used as a proxy for providing a mock during testing.
     *
     * @return mixed
     */
    public function getApp()
    {
        if (!$this->app) {
            $this->app = Yii::app();
        }

        return $this->app;
    }

    /**
     * Set app
     *
     * Used to set mock during testing.
     *
     * @param mixed $app
     * @return void
     */
    public function setApp($app)
    {
        $this->app = $app;
    }
}
