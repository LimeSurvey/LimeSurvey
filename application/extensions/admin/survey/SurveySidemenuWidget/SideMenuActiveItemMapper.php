
<?php

/**
 * Maps and identifies active menu items in the survey side menu.
 *
 * This class determines whether a menu item should be marked as active
 * by comparing URLs and checking against defined patterns and sub-items.
 */
class SideMenuActiveItemMapper extends WhSelect2
{
    /** @var string The type of menu being processed */
    public $menutype;

    /** @var int The survey ID */
    public $surveyid;

    /** @var string The current request URL */
    public $currentUrl;

    /**
     * Determines if a menu item URL matches the current page.
     *
     * This method checks if the provided menu item URL matches the current URL
     * either directly or through sub-item matching.
     *
     * @param string $menuItemUrl The URL of the menu item to check.
     * @param string $menutype The type of menu (e.g., 'statistics', 'responses').
     * @param int $surveyid The ID of the current survey.
     * @return bool True if the menu item matches the current page, false otherwise.
     */
    public function match($menuItemUrl, $menutype, $surveyid)
    {
        $this->surveyid = $surveyid;
        $this->menutype = strtolower($menutype);
        $this->currentUrl = App()->request->requestUri;
        $sanitizedUrl = $this->normalizeUrl($menuItemUrl);
        $normalizedCurrentUrl = $this->normalizeUrl($this->currentUrl);

        if (
            $sanitizedUrl === $normalizedCurrentUrl
            || $this->matchSubItems()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the current URL matches any sub-items for the current menu type.
     *
     * This method compares the current URL against both specific URLs and patterns
     * defined for the menu type's sub-items.
     *
     * @return bool True if a sub-item match is found, false otherwise.
     */
    public function matchSubItems()
    {
        $allowedSubItems = $this->getAllowedSubItems();

        if (!array_key_exists($this->menutype, $allowedSubItems)) {
            return false;
        }

        $normalizedCurrentUrl = $this->normalizeUrl($this->currentUrl);
        $menuConfig = $allowedSubItems[$this->menutype];

        // Check specific URLs
        foreach ($menuConfig['urls'] as $url) {
            $normalizedUrl = $this->normalizeUrl($url);
            if ($normalizedCurrentUrl === $normalizedUrl) {
                return true;
            }
        }

        // Check pattern match
        if (isset($menuConfig['pattern'])) {
            $pattern = $menuConfig['pattern'];
            if (strpos($normalizedCurrentUrl, $pattern) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the configuration of allowed sub-items for each menu type.
     *
     * Sub-items are additional pages that belong to a main menu category
     * but have different URLs than the main menu item URL. Each menu type
     * can define specific URLs and/or URL patterns for matching.
     *
     * Pattern matching uses strpos() to check if the pattern appears at the
     * start of the URL (position 0), so patterns must match the beginning of URLs.
     *
     * @return array Associative array of menu types with their patterns and URLs.
     *               Structure: [
     *                   'menutype' => [
     *                       'pattern' => string, // URL pattern to match (from start)
     *                       'urls' => array      // Specific URLs to match
     *                   ]
     *               ]
     */
    public function getAllowedSubItems()
    {
        return array(
            'statistics' => [
                'pattern' => '/admin/statistics/sa/',
                'urls' => []
            ],
            'participants' => [
                'pattern' => '/admin/tokens',
                'urls' => []
            ],
            'responses' => [
                'pattern' => '/responses/',
                'urls' => [
                    App()->createUrl("admin/export/sa/exportresults/surveyid/$this->surveyid"),
                    App()->createUrl("admin/export/sa/exportspss/sid/$this->surveyid"),
                    App()->createUrl("admin/export/sa/vvexport/surveyid/$this->surveyid"),
                    App()->createUrl("admin/dataentry/sa/vvimport/surveyid/$this->surveyid"),
                    App()->createUrl("admin/dataentry/sa/iteratesurvey/surveyid/$this->surveyid"),
                    App()->createUrl("admin/dataentry/sa/import/surveyid/$this->surveyid"),
                    App()->createUrl("/admin/dataentry/sa/view/surveyid/$this->surveyid"),
                ]
            ],
            'quotas' => [
                'pattern' => '/quotas/',
                'urls' => []
            ]
        );
    }

    /**
     * Normalizes a URL by removing index.php and trailing slashes.
     *
     * This method standardizes URLs for comparison by removing the "/index.php"
     * segment and any trailing slashes, ensuring consistent URL matching.
     *
     * @param string $url The URL to normalize.
     * @return string The normalized URL without "/index.php" and trailing slashes.
     */
    private function normalizeUrl($url)
    {
        $normalizedUrl = str_replace("/index.php", "", $url);
        return rtrim($normalizedUrl, '/');
    }
}
