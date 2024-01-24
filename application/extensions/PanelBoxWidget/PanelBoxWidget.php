<?php

class PanelBoxWidget extends CWidget
{
    public $fromDb = false; // If set to 1, the widget will look for the box definition inside the database
    public $dbPosition = 1; // Id of the box in the database
    public $position; // Position of the box in the list
    public $url;
    public $title;
    public $ico;
    public $description;
    public $buttontext;
    public $usergroup;
    public $offset = 3;
    public $display = 'singlebox';
    public $boxesbyrow = 3;
    public $external = false;
    public $boxesincontainer = false;

    public function run()
    {
        App()->getClientScript()->registerPackage('panelboxes');
        if ($this->display == 'singlebox') {
            if ($this->fromDb) {
                $this->setValuesFromDb();
            }

            return $this->renderBox();
        } elseif ($this->display = 'allboxesinrows') {
            return $this->renderRows();
        }
    }

    public function getBoxes()
    {
        $boxes = Box::model()->findAll(array('order' => 'position ASC'));
        return $boxes;
    }

    protected function setValuesFromDb()
    {
        $box = Box::model()->find(array(
            'condition' => 'position=:positionId',
            'params' => array(':positionId' => $this->dbPosition)
        ));
        if ($box) {
            $this->position = $box->position;
            if (!preg_match("/^(http|https)/", (string) $box->url)) {
                $this->url = Yii::app()->createUrl($box->url);
            } else {
                $this->url = $box->url;
                $this->external = true;
            }
            $this->title = $box->title;
            $this->buttontext = $box->buttontext ?? $box->title;
            $this->ico = $box->getIconName();
            $this->description = $box->desc;
            $this->usergroup = $box->usergroup;
        } else {
            $this->position = '1';
            $this->url = '';
            $this->title = gT('Error');
            $this->buttontext = gT('Error');
            $this->description = gT('Unknown box ID!');
        }
    }

    /**
     * Render a single box
     */
    protected function renderBox()
    {
        if (self::canSeeBox()) {
            $offset = ($this->offset != '') ? 'offset-md-1 offset-xl-' . $this->offset : '';

            $this->render('box', array(
                'position' => $this->position,
                'offset' => $offset,
                'url' => $this->url,
                'title' => $this->title,
                'ico' => $this->ico,
                'description' => $this->description,
                'buttontext' => $this->buttontext,
                'external' => $this->external,
                'sizeClass' => "col-lg-".(12/$this->boxesbyrow)." col-md-".(floor(24/$this->boxesbyrow)) . " col-xs-12"
            ));
        }
    }

    /**
     * Render all boxes in row
     */
    protected function renderRows()
    {
        // We get all the boxes in the database
        $boxes = self::getBoxes();
        $boxcount = 0;
        $bIsRowOpened = false;
                $this->render('row_header', array(
                    'orientation' => $this->getOrientationClass(),
                    'containerclass' => ($this->boxesincontainer ? 'container' : '')
                ));
        foreach ($boxes as $box) {

             $this->controller->widget('ext.PanelBoxWidget.PanelBoxWidget', array(
                 'display' => 'singlebox',
                 'fromDb' => true,
                 'dbPosition' => $box->position,
                 'offset' => '',
                 'boxesbyrow' => $this->boxesbyrow
             ));

        }
            $this->render('row_footer');
    }

    protected function canSeeBox($box = '')
    {
        $box = ($box == '') ? $this : $box;
        if ($box->usergroup == '-1') {
            return true;
        } // If the user group is not set, or set to -2, only admin can see the box
        elseif (empty($box->usergroup) || $box->usergroup == '-2') {
            if (Permission::model()->hasGlobalPermission('superadmin', 'read') ? 1 : 0) {
                return true;
            } else {
                return false;
            }
        } // If user group is set to -3, nobody can see the box
        elseif ($box->usergroup == '-3') {
            return false;
        } // If usegroup is set and exist, if the user belong to it, he can see the box
        else {
            $oUsergroup = UserGroup::model()->findByPk($box->usergroup);

            // The group doesn't exist anymore, so only admin can see it
            if (!is_object($oUsergroup)) {
                if (Permission::model()->hasGlobalPermission('superadmin', 'read') ? 1 : 0) {
                    return true;
                } else {
                    return false;
                }
            }

            if (Yii::app()->user->isInUserGroup($box->usergroup)) {
                return true;
            }
        }
    }

    private function getOrientationClass(){
        switch($this->offset){
            case 1: return 'align-content-flex-start'; break;
            case 2: return 'align-content-flex-end'; break;
            case 3: //fallthrough
           default: return 'align-content-space-around'; break;
        }
    }

}
