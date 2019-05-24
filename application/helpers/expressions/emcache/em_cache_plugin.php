<?php

class EmCachePlugin extends PluginBase
{
    /**
     * @return void
     */
    public function beforeModelSave()
    {
        $event = $this->getEvent();
    }
}
