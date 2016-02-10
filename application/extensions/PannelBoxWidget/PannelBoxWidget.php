<?php

    class PannelBoxWidget extends CWidget
    {
        public $fromDb=FALSE; // If set to 1, the widget will look for the box definition inside the database
        public $dbPosition=1; // Id of the box in the database
        public $position; // Position of the box in the list
        public $url;
        public $title;
        public $ico;
        public $description;
        public $usergroup;
        public $offset=3;
        public $display='singlebox';
        public $boxesbyrow=3;

        public function run()
        {
            if($this->display=='singlebox')
            {
                if($this->fromDb)
                {
                    $this->setValuesFromDb();
                }

                return $this->renderBox();
            }
            elseif($this->display='allboxesinrows')
            {
                return $this->renderRows();
            }
        }

        public function getBoxes()
        {
            $boxes = Boxes::model()->findAll();
            return $boxes;
        }

        protected function setValuesFromDb()
        {
            $box = Boxes::model()->find(array('condition'=>'position=:positionId', 'params'=>array(':positionId'=>$this->dbPosition)));
            if($box)
            {
                $this->position = $box->position;
                $this->url = $box->url;
                $this->title = $box->title;
                $this->ico = $box->ico;
                $this->description = $box->desc;
                $this->usergroup = $box->usergroup;
            }
            else
            {
                $this->position = '1';
                $this->url = '';
                $this->title = gT('Error');
                $this->description = gT('Unknown box ID!');
            }
        }

        /**
         * Render a single box
         */
        protected function renderBox()
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

        /**
         * Render all boxes in row
         */
        protected function renderRows()
        {
            // We get all the boxes in the database
            $boxes = Boxes::model()->findAll();
            $boxcount = 0;
            foreach($boxes as $box)
            {
                $boxcount=$boxcount+1;
                // It's the first box, we must display row header, and have an offset
                if($boxcount == 1)
                {
                    $this->render('row_header');
                    $bIsRowOpened = true;
                    $this->controller->widget('ext.PannelBoxWidget.PannelBoxWidget', array(
                                'display'=>'singlebox',
                                'fromDb'=> true,
                                'dbPosition'=>$box->position,
                                'offset' =>$this->offset,
                        ));
                }
                else
                {
                    $this->controller->widget('ext.PannelBoxWidget.PannelBoxWidget', array(
                                    'display'=>'singlebox',
                                    'fromDb'=> true,
                                    'dbPosition'=>$box->position,
                                    'offset' =>'',
                        ));
                }

                // If it is the last box, we should close the box
                if($boxcount == $this->boxesbyrow)
                {
                        $this->render('row_footer');
                        $boxcount = 0;
                        $bIsRowOpened = false;
                }
            }

            // If the last row has not been closed, we close it
            if($bIsRowOpened == true)
            {
                $this->render('row_footer');
            }
        }
    }
