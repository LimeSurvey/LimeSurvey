<?php

class SideMenuActiveItemMapper extends WhSelect2
{
    public $menutype;
    public $surveyid;
    public $currentUrl;

    public function match($menuItemUrl, $menutype, $surveyid)
    {
        $this->surveyid = $surveyid;
        $this->menutype = strtolower($menutype);
        $this->currentUrl = App()->request->requestUri;
        $santizedUrl = str_replace("/index.php", "", $menuItemUrl);
        if (
            $menuItemUrl == $this->currentUrl
            ||  $santizedUrl == $this->currentUrl
            ||  $this->matchSubItems()
        ) {
            return true;
        }

        return false;
    }

    public function matchSubItems()
    {
        if (array_key_exists($this->menutype, $this->getAllowedSubItems())) {
            $normalizedCurrentUrl = str_replace("/index.php", "", $this->currentUrl);

            foreach ($this->getAllowedSubItems()[$this->menutype]['urls'] as $url) {
                $normalizedUrl = str_replace("/index.php", "", $url);
                if ($normalizedCurrentUrl === $normalizedUrl) {
                    return true;
                }
            }

            // Check if there's a pattern match
            if (isset($this->getAllowedSubItems()[$this->menutype]['pattern'])) {
                $pattern = $this->getAllowedSubItems()[$this->menutype]['pattern'];
                if (strpos($normalizedCurrentUrl, $pattern) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Subitems are additional pages which belong to the main category,
     * but have different urls than the provided menuItemUrl.
     * We can add specific urls here, and/or general patterns.
     * @return array
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
                ]
            ],
            'quotas' => [
                'pattern' => '/quotas/',
                'urls' => []
            ]
        );
    }
}
