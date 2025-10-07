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
        if (
            array_key_exists($this->menutype, $this->getAllowedSubItems())
            && in_array($this->currentUrl, $this->getAllowedSubItems()[$this->menutype]['urls'])
        ) {
            return true;
        }
    }

    public function getAllowedSubItems()
    {
        return array(
            'statistics' =>  [
                'urls' => [
                    App()->createUrl('/admin/statistics/sa/simpleStatistics/surveyid/' . $this->surveyid),
                ]
            ],
            'participants' => [
                'urls' => [
                    App()->createUrl("admin/tokens/sa/managetokenattributes/surveyid/$this->surveyid"),
                    App()->createUrl("admin/tokens/sa/exportdialog/surveyid/$this->surveyid")
                ]
            ]
        );
    }

}
