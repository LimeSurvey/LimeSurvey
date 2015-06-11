<?php

class AssetManager extends CAssetManager {


    public function getBaseUrl() {

        return strtr(parent::getBaseUrl(), ['{baseUrl}' => App()->baseUrl]);
    }
}