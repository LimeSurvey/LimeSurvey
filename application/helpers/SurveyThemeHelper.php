<?php

/*
* LimeSurvey
* Copyright (C) 2007-2021 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/** 
 * About Theme Options Path Treatment - Theme Options Path Prefix
 * =========================
 *
 * Path sanitization is applied to all Theme Options ('options' attribute) that match an existing path or a "virtual" path.
 *
 * The paths allowed in Theme Options are restricted to three categories:
 *
 * - General Files: Files under <userthemerootdir>/generalfiles
 * - Theme Files: Files under the theme folder
 * - Survey Files: Files under <uploaddir>/surveys/<sid>/images
 *
 * Please note that the paths must point to files inside those folders, so path traversal is not allowed.  
 *
 * To be clear about which of those categories the path belongs to, a prefix is added, making it a "virtual" path. 
 * - General Files: image::generalfiles::
 * - Theme Files: image::theme::
 * - Survey Files: image::survey::
 *
 * Paths are considered invalid if:  
 * - The path starts with one of the prefixes mentioned above but the file doesn't exist inside the category's folder.  
 * - The path matches a real path to an existing file 
 *   (either relative to the root of LS installation, to the current working dir or absolute),
 *   but the file is not inside one of the categories folders.
 * 
 * After sanitization, valid paths are converted to virtual paths, and invalid paths are prefixed with "invalid:".
 *
 * NOTE: Paths that don't have one of the category prefixes but don't match an existing file are left untouched, 
 *       because there is no way to be  100% * sure that they are actual paths.
 */


use LimeSurvey\Datavalueobjects\ThemeFileCategory;
use LimeSurvey\Datavalueobjects\ThemeFileInfo;

/**
 * General helper class for survey themes
 */
class SurveyThemeHelper
{
    /**
     * Returns the virtual path prefix of $virtualPath.
     *
     * @param string $virtualPath
     * @return string|null the virtual path prefix, or null if $virtualPath doesn't match the format
     */
    public static function getVirtualPathPrefix($virtualPath)
    {
        if (preg_match('/(image::\w+::)/', $virtualPath, $m)) {
            return $m[1];
        } else {
            return null;
        }
    }

    /**
     * Returns true if $value matches the virtual path format.
     * It doesn't check the path validity.
     *
     * @param string $value
     * @return boolean
     */
    public static function isVirtualPath($value)
    {
        return !empty(self::getVirtualPathPrefix($value));
    }

    /**
     * Returns a list of themes found in the $folder, in the form
     * of an array where the key is the theme name, and value is
     * the theme's path.
     * @param string $folder
     * @return array<string,string>
     */
    public static function getTemplateInFolder($folder)
    {
        /** @var array<string,string> */
        $templateList = [];

        if ($folder && $handle = opendir($folder)) {
            while (false !== ($fileName = readdir($handle))) {
                if (
                    !is_file("$folder/$fileName")
                    && $fileName != "."
                    && $fileName != ".."
                    && $fileName != ".svn"
                    && $fileName != "generalfiles"
                    //&& (file_exists("{$folder}/{$fileName}/config.xml"))
                ) {
                    $templateList[$fileName] = $folder . DIRECTORY_SEPARATOR . $fileName;
                }
            }
            closedir($handle);
        }
        ksort($templateList);
        return  $templateList;
    }

    public static function getNestedThemeConfigPath($templateName) {
        $directory = Yii::app()->getConfig("userthemerootdir") . DIRECTORY_SEPARATOR . $templateName;
        $paths = CFileHelper::findFiles($directory, ['level' => 200]);
        foreach ($paths as $path) {
            if (str_contains($path, 'config.xml')) {
                return substr($path, 0, strrpos($path, '/') + 1);
            }
        }
        return null;
    }

    /**
     * Returns a list of user themes, in the form of an array where
     * the key is the folder name, and value is the theme's path.
     * @return array<string,string>
     */
    public static function getTemplateInUpload()
    {
        /** @var array<string,string> used for caching */
        static $templatesInUploadDir = null;
        if (empty($templatesInUploadDir)) {
            $userTemplateRootDir = Yii::app()->getConfig("userthemerootdir");
            $templatesInUploadDir = self::getTemplateInFolder($userTemplateRootDir);
        }

        return $templatesInUploadDir;
    }

    /**
     * Returns a list of standard themes, in the form of an array where
     * the key is the folder name, and value is the theme's path.
     * @return array<string,string>
     */
    public static function getTemplateInStandard()
    {
        /** @var array<string,string> used for caching */
        static $templatesInStandardDir = null;
        if (empty($templatesInStandardDir)) {
            $standardTemplateRootDir = Yii::app()->getConfig("standardthemerootdir");
            $templatesInStandardDir = self::getTemplateInFolder($standardTemplateRootDir);
        }
        return $templatesInStandardDir;
    }

    /**
     * Return the standard template list
     * @return string[]
     * @throws Exception
     */
    public static function getStandardTemplateList()
    {
        return array_keys(self::getTemplateInStandard());
    }

    /**
     * isStandardTemplate returns true if a template is a standard template.
     * This function does not check if a template actually exists.
     * Scans standard themes folder and looks for folder matching the $themeName.
     * Important: here is asumed that theme name = folder name
     *
     * @param mixed $themeName template name to look for
     * @return bool True if standard template, otherwise false
     */
    public static function isStandardTemplate($themeName)
    {
        $standardTemplates = self::getStandardTemplateList();
        return in_array($themeName, $standardTemplates);
    }

    /**
     * Returns a path's ThemeFileInfo if it's an absolute path or relative to the root dir.
     * The function returns false if the path is not found, and null if it's found but doesn't
     * match a category.
     * @param string $path
     * @param LimeSurvey\Datavalueobjects\ThemeFileCategory[] $categoryList
     * @return LimeSurvey\Datavalueobjects\ThemeFileInfo|null|false
     */
    public static function getThemeFileInfoFromAbsolutePath($path, $categoryList)
    {
        // Check if the path is relative to the root dir or an absolute path
        $absolutePath = realpath(Yii::app()->getConfig('rootdir') . '/' . $path);
        if ($absolutePath === false && strpos($path, '/') === 0) {
            $absolutePath = realpath($path);
        }
        // If the path was absolute (or relative to the root dir), we check if it's within
        // a category's bounds.
        if (!empty($absolutePath)) {
            foreach ($categoryList as $category) {
                // Get real path for the category
                $categoryPath = realpath($category->path);
                if (empty($categoryPath)) {
                    continue;
                }
                $categoryPath = $categoryPath . DIRECTORY_SEPARATOR;
                if (strpos($absolutePath, $categoryPath) === 0) {
                    $virtualPath = str_replace($categoryPath, $category->pathPrefix, $absolutePath);
                    return new ThemeFileInfo($absolutePath, $virtualPath, $category);
                }
            }
            // The path didn't belong to any category
            return null;
        }
        return false;
    }

    /**
     * Returns a path's ThemeFileInfo if it's relative to a category.
     * The function returns false if the path is not relative to any category.
     * @param string $path
     * @param LimeSurvey\Datavalueobjects\ThemeFileCategory[] $categoryList
     * @return LimeSurvey\Datavalueobjects\ThemeFileInfo|false
     */
    public static function getThemeFileInfoFromRelativePath($path, $categoryList)
    {
        foreach ($categoryList as $category) {
            // Get real path for the category
            $categoryPath = realpath($category->path);
            if (empty($categoryPath)) {
                continue;
            }
            $categoryPath = $categoryPath . DIRECTORY_SEPARATOR;

            // Get the realpath for the file.
            $realPath = realpath($categoryPath . $path);

            // If the path is not found try with next category
            if ($realPath === false) {
                continue;
            }

            // Ok, now we know the path exists and is relative to the category, but
            // it could be traversing out.
            // Now let's check if it's within this category's bounds.

            // If the real path starts with category's path, we return the file info.
            if (strpos($realPath, $categoryPath) === 0) {
                $virtualPath = str_replace($categoryPath, $category->pathPrefix, $realPath);
                return new ThemeFileInfo($realPath, $virtualPath, $category);
            }
        }
        return false;
    }

    /**
     * Returns a list of file categories for the theme.
     * Each category is related to a directory which holds files for the theme.
     * This files are usually listed to be selected as values for options.
     *
     * @param string $themeName
     * @param mixed $sid
     * @return LimeSurvey\Datavalueobjects\ThemeFileCategory[]
     */
    public static function getFileCategories($themeName, $sid = null)
    {
        // We need to determine the paths first. They may already be set, but if they're not, we need to get them from the template.
        // Note that the template cannot be accessed as relation until the model is saved.
        $path = self::getThemePath($themeName);
        $generalFilesPath = Yii::app()->getConfig("userthemerootdir") . DIRECTORY_SEPARATOR . 'generalfiles' . DIRECTORY_SEPARATOR;

        /** @var LimeSurvey\Datavalueobjects\ThemeFileCategory[] */
        $categoryList = [];
        $categoryList[] = new ThemeFileCategory('generalfiles', gT("Global"), $generalFilesPath, 'image::generalfiles::');
        $categoryList[] = new ThemeFileCategory('theme', gT("Theme"), $path, 'image::theme::');
        if (!empty($sid)) {
            $categoryList[] = new ThemeFileCategory('survey', gT("Survey"), Yii::app()->getConfig('uploaddir') . '/surveys/' . $sid . '/images/', 'image::survey::');
        }
        return $categoryList;
    }

    /**
     * Returns the path to the theme specified by $themeName.
     * @param string $themeName
     * @return string
     */
    public static function getThemePath($themeName)
    {
        $basePath = self::isStandardTemplate($themeName) ? Yii::app()->getConfig("standardthemerootdir") : Yii::app()->getConfig("userthemerootdir");

        // Technically the theme's folder name is saved in the model ($template->folder),
        // but we use the theme name here, to avoid using the model and/or database calls.
        // Throughout the code, it is asumed that the theme's folder matches the theme name.
        // Seems that asumption has it's root source in the isStandardTemplate() method.
        $path = $basePath . DIRECTORY_SEPARATOR . $themeName . DIRECTORY_SEPARATOR;
        return $path;
    }

    /**
     * Validates $path and returns its file info
     * Rules:
     *  - valid virtual path
     *  - real path for an available category
     *
     * @param string|null $path  the path to check. Can be a "virtual" path (eg. 'image::theme::logo.png'), or a normal path.
     * @param string $themeName
     * @param mixed $sid
     * @return LimeSurvey\Datavalueobjects\ThemeFileInfo|null the file info if it's valid, or null if it's not.
     */
    public static function getThemeFileInfo($path, $themeName, $sid = null)
    {
        if (is_null($path)) {
            return null;
        }

        /** @var LimeSurvey\Datavalueobjects\ThemeFileCategory[] */
        $categoryList = self::getFileCategories($themeName, $sid);

        // Check if the path matches the virtual path category format
        $prefix = self::getVirtualPathPrefix($path);
        if (!empty($prefix)) {
            // Find category that matches the prefix
            $filteredCategories = array_filter($categoryList, function ($v) use ($prefix) {
                return $v->pathPrefix == $prefix;
            });
            if (empty($filteredCategories)) {
                return null;    // No category matched the path's prefix
            }
            $category = reset($filteredCategories);
            $categoryPath = realpath($category->path) . DIRECTORY_SEPARATOR;

            // Validate that the file exists
            $realPath = realpath($categoryPath . '/' . substr($path, strlen($prefix)));

            // If the file exists and no traversing is done (the real path starts with the category's base path),
            // return the file info
            if ($realPath !== false && strpos($realPath, $categoryPath) === 0) {
                $virtualPath = str_replace($categoryPath, $category->pathPrefix, $realPath);
                return new ThemeFileInfo($realPath, $virtualPath, $category);
            } else {
                return null;
            }
        }

        // Path doesn't match the virtual path category format, so we try the determine if it belongs to a category.

        // Handle the case of absolute paths and paths relative to the root dir.
        $result = self::getThemeFileInfoFromAbsolutePath($path, $categoryList);
        if ($result !== false) {
            return $result;
        }

        // If we got here, the path was not absolute (nor relative to the root dir), so we check
        // if it's relative to a category.
        $result = self::getThemeFileInfoFromRelativePath($path, $categoryList);
        if ($result !== false) {
            return $result;
        }

        // The path didn't belong to any category
        return null;
    }

    /**
     * Returns the virtual path for $path.
     *
     * @param string $path  the path to check. Can be a "virtual" path (eg. 'image::theme::logo.png'), or a normal path.
     * @param string $themeName
     * @param mixed $sid
     * @return string|null the virtual path if it's valid, of null if it's not.
     */
    public static function getVirtualThemeFilePath($path, $themeName, $sid = null)
    {
        /** @var LimeSurvey\Datavalueobjects\ThemeFileInfo|null */
        $fileInfo = self::getThemeFileInfo($path, $themeName, $sid);

        if (empty($fileInfo)) {
            return null;
        }

        return $fileInfo->virtualPath;
    }

    /**
     * Returns the real aboslute path of $path
     * If $path is not valid, returns null.
     *
     * @param string $path  the path to check. Can be a "virtual" path (eg. 'image::theme::logo.png'), or a normal path.
     * @param string $themeName
     * @param mixed $sid
     * @return string|null the real absolute path if it's valid, of null if it's not.
     */
    public static function getRealThemeFilePath($path, $themeName, $sid = null)
    {
        /** @var LimeSurvey\Datavalueobjects\ThemeFileInfo|null */
        $fileInfo = self::getThemeFileInfo($path, $themeName, $sid);

        if (empty($fileInfo)) {
            return null;
        }

        return $fileInfo->realPath;
    }

    /**
     * Sanitizes a theme option value making sure that paths are valid.
     *
     * - All paths should be relative to the root directoy of the current theme or general files.
     * - All paths should be a subdir of the current theme or general files -no path traversal (.. or . ) will be allowed - (example: "../../files/image.png" is not allowed)
     *
     * Options that match a file will be marked as invalid if the file
     * is not valid, or replaced with the virtual path if the file is valid.
     * The validity of paths depend on the theme configuration (basically the
     * $themeName and the $sid, which could be empty for global options).
     *
     * @param string $value
     * @param string $themeName
     * @param string $sid
     * @return string
     */
    public static function sanitizePathInOption($value, $themeName, $sid = null)
    {
        // We only sanitize strings
        if (!is_string($value)) {
            return;
        }

        // This is used to sanitize all options of the theme. Not only classic ones which
        // are expected to hold a path, as other options may hold a path as well (eg. custom theme options)
        if (empty($value) || $value == 'inherit') {
            return $value;
        }
        // If the value starts with 'invalid:', skip it.
        if (stripos($value, 'invalid:', 0) === 0) {
            return $value;
        }
        // Validation A - If option value is a path that matches a virtual path, transform the value to the virtual path
        $virtualPath = self::getVirtualThemeFilePath($value, $themeName, $sid);
        if (!empty($virtualPath)) {
            $value = $virtualPath;
            return $value;
        }
        // Validation B - If the file couldn't be matched to a category (validation A) we flag it as invalid if:
        // - option value matches a virtual path format but is invalid or
        // - option value matches a real existing path to a file either relative to the root LS installation or to the current workgin dir or absolute
        // Mark the value as invalid, as that's not allowed. These are files outside the boundaries.
        if (self::isVirtualPath($value) || realpath($value) !== false || realpath(Yii::app()->getConfig('rootdir') . '/' . $value) !== false) {
            $value = 'invalid:' . $value;
            return $value;
        }
        // Validation C - If the value contains certain substrings, we try to convert it into some known dirs.
        $replacements = [
            "~^.*themes[\\/]survey~" => [
                Yii::app()->getConfig("standardthemerootdir"),
                Yii::app()->getConfig("userthemerootdir")
            ]
        ];
        $validPathFound = false;
        foreach ($replacements as $pattern => $alternatives) {
            // If the value matches the pattern, we replace that part by each of the alternative replacements,
            // and try to get a valid virtual path from that.
            if (preg_match($pattern, $value, $m)) {
                foreach ($alternatives as $replacement) {
                    $path = preg_replace($pattern, (string) $replacement, $value);
                    $virtualPath = self::getVirtualThemeFilePath($path, $themeName, $sid);
                    if (!empty($virtualPath)) {
                        $value = $virtualPath;
                        $validPathFound = true;
                        break;
                    }
                }
                if ($validPathFound) {
                    break;
                }
            }
        }
        if ($validPathFound) {
            return $value;   // Not needed at the moment, because we are at the end of the method. But it's clearer in case another validation is added later.
        }
        // If we got here, it means the value couldn't be matched to real path.
        // It may look like a path (maybe a file that no longer exists), or be something completely different.
        return $value;
    }

    /**
     * Checks and updates the given configuration file if necessary.
     *
     * This function loads the specified XML configuration file into a DOMDocument object, checks for its validity,
     * and if applicable, updates it by calling `checkDomDocument`. If the file is invalid or an exception occurs,
     * a warning is logged with details about the issue.
     *
     * @param string $configFile Path to the configuration file to be checked and potentially updated.
     *
     * @return void This function does not return a value. It may either update the configuration file
     *              or log a warning if the file is invalid or cannot be processed.
     *
     * @throws \Exception Propagates any exceptions thrown by `checkDomDocument`.
     */
    public static function checkConfigFiles($configFile)
    {
        $domDocument = new \DOMDocument;
        $domDocument->load($configFile);
        if (!$domDocument) {
            \Yii::log('Invalid config file at ' . $configFile, \CLogger::LEVEL_WARNING, 'application');
            return;
        }
        try {
            $newDomDocument = self::checkDomDocument($domDocument);
            if ($newDomDocument) {
                $newDomDocument->save($configFile);
            }
        } catch (\Exception $e) {
            \Yii::log('Error: ' . $e->getMessage() . 'found in ' . $configFile, \CLogger::LEVEL_WARNING, 'application');
        }
    }

    /**
     * Processes a DOMDocument object to check and potentially modify its structure.
     *
     * This method specifically looks for 'cssframework' nodes within the given DOMDocument.
     * If found, it examines child nodes for a default option and 'dropdownoptions'. It ensures that
     * all 'option' nodes are wrapped within an 'optgroup' element. If any modifications are made,
     * the DOMDocument is marked as changed.
     *
     * @param \DOMDocument $domDocument The DOMDocument object to be checked and potentially modified.
     *
     * @return \DOMDocument|null Returns the modified DOMDocument if changes were made, otherwise null.
     *                           Changes include ensuring 'option' nodes within 'cssframework' are properly
     *                           grouped under an 'optgroup' and setting a default option if not present.
     *
     * @throws \Exception If an invalid node is found within 'dropdownoptions' or if no 'dropdownoptions'
     *                    nodes are found when expected.
     */
    private static function checkDomDocument($domDocument)
    {

        $isChangedDomDocument = false;

        // Find first 'cssframework' nodes in the document
        $cssFrameworkNodes = $domDocument->getElementsByTagName('cssframework');
        if ($cssFrameworkNodes) {
            $cssFrameworkNode = $cssFrameworkNodes->item(0);
        }

        if ($cssFrameworkNode) {

            $defaultOption = '';
            $dropDownOptionsNode = null;

            foreach ($cssFrameworkNode->childNodes as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $deafultOption = $child->nodeValue;
                } elseif ($child->nodeName === 'dropdownoptions') {
                    $dropDownOptionsNode = $child;
                }
            }

            if ($dropDownOptionsNode) {
                $optGroupNode = $dropDownOptionsNode->getElementByTag('optgroup');
                if (!$optGroupNode) {

                    // Create a new 'optgroup' element
                    $optGroupNode = $domDocument->createElement('optgroup');

                    // Loop through all 'option' nodes and move them to 'optgroup'
                    while ($dropDownOptionsNode->childNodes->length > 0) {
                        $optionNode = $dropDownOptionsNode->firstChild;
                        if ($optionNode->nodeName != 'option') {
                            throw new \Exception('Invalid node in the config file.');
                        }
                        $optGroupNode->appendChild($optionNode);
                    }

                    // Append the 'optgroup' with all the 'option' nodes into 'dropdownoptions'
                    $dropDownOptionsNode->appendChild($optGroupNode);
                    $isChangedDomDocument = true;
                }
            } else {
                throw new \Exception('No "dropdownoptions" nodes were found.');
            }

            if ($defaultOption === '') {
                $defaultOption = $optGroupNode->firsChild->nodeValue;
                if (is_string($defaultOption)) {
                    $textNode = $domDocument->createTextNode($defaultOption);
                    $cssFrameworkNode->insertBefore($textNode, $dropDownOptionsNode);
                    $isChangedDomDocument = true;
                }
            }
        }
        if ($isChangedDomDocument) {
            return $domDocument;
        }
        return null;
    }
}
