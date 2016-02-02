<?php

    class PannelBoxWidget extends CWidget
    {
        public $fromDb=FALSE; // If set to 1, the widget will look for the box definition inside the database
        public $dbPosition=1; // Id of the box in the database
        public $position; // Position of the box in the list
        public $url;
        public $title;
        public $img;
        public $ico;
        public $description;
        public $offset='';

        public function run()
        {
            if($this->fromDb)
            {
                $this->setValuesFromDb();
            }
            return $this->renderContent();
        }

        public function getBoxes()
        {
            $boxes = Boxes::model()->findAll();
        }

        protected function setValuesFromDb()
        {
            $box = Boxes::model()->find(array('condition'=>'position=:positionId', 'params'=>array(':positionId'=>$this->dbPosition)));
            if($box)
            {
                $this->position = $box->position;
                $this->url = $box->url;
                $this->title = $box->title;
                $this->img = $box->img;
                $this->ico = $box->ico;
                $this->description = $box->desc;
            }
            else
            {
                $this->position = '1';
                $this->url = '';
                $this->title = gT('Error');
                $this->img = '';
                $this->description = gT('Unknown box ID!');
            }
        }

        protected function renderContent()
        {
            $offset = ($this->offset != '') ? 'col-sm-offset-1 col-lg-offset-'.$this->offset : '';



            $this->render('box', array(
                'position'=> $this->position,
                'offset' => $offset,
                'url'=> Yii::app()->createUrl($this->url),
                'title'=> $this->title,
                'ico'=> $this->ico,
                'description'=> $this->description,
            ));
        }
    }
