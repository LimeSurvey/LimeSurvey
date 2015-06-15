<?php

class AssetManager extends CAssetManager {


    public function getBaseUrl() {

        /**
         * @todo Solve this in a more efficient way, for example by storing the result.
         *
         */
        return strtr(parent::getBaseUrl(), ['{baseUrl}' => App()->baseUrl]);
    }
}