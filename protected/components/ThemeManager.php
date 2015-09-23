<?php

class ThemeManager extends CThemeManager {
    
	/**
	 * @param string $name name of the theme to be retrieved
	 * @return CTheme the theme retrieved. Null if the theme does not exist.
	 */
	public function getTheme($name)
	{
		$themePath=$this->getBasePath().DIRECTORY_SEPARATOR.$name;
		if(is_dir($themePath)) {
			$class=Yii::import($this->themeClass, true);
            // We publish all assets for the theme.
            $assetPath = $themePath . '/assets';
            if (is_dir($assetPath)) {
                $url = App()->assetManager->publish($assetPath, false, -1, YII_DEBUG);
            } else {
                $url = $this->getBaseUrl().'/'.$name;
            }
			return new $class($name, $themePath, $url);
		}
		else
			return null;
	}
}