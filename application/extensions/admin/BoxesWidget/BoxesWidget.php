<?php

/**
 * BoxesWidget is a widget that renders a set of configurable boxes, which can represent products, product groups, or links.
 *
 * @property array $items The array of items to be rendered as boxes.
 * @property int $limit The maximum number of boxes to display.
 * @property bool $searchBox Whether to include a search box in the widget.
 * @property bool $switch Controls the rendering of the view widget.
 * @const int TYPE_PRODUCT Represents a product type.
 * @const int TYPE_PRODUCT_GROUP Represents a product group type.
 * @const int TYPE_LINK Represents a link type.
 *
 * @throws CException If an error occurs during widget execution.
 */
class BoxesWidget extends CWidget
{
    const TYPE_PRODUCT = 0;
    const TYPE_PRODUCT_GROUP = 1;
    const TYPE_LINK = 2;
    const TYPE_PLACEHOLDER = 3;
    public array $items = [];
    public int $limit = 5;
    public bool $searchBox = true;
    /**
     * For rendering the switch to decide which view widget is rendered
     * @var $switch bool
     */
    public bool $switch = false;

    /** Initializes the widget */
    public function init(): void
    {
        $this->registerClientScript();
    }

    /** Registers required script files */
    public function registerClientScript(): void
    {
        App()->getClientScript()->registerScriptFile(
            App()->getConfig("extensionsurl") . 'admin/BoxesWidget/assets/boxes-widget.js',
            CClientScript::POS_END
        );
    }

    /** Executes the widget
     * @throws CException
     */
    public function run()
    {
        $boxes = [];
        $itemsCount = 0;
        foreach ($this->items as $item) {
            $item = (object)$item;

            if (isset($item->type) && $item->type == self::TYPE_LINK) {
                $boxes[] = [
                    'link' => $item->link,
                    'type' => self::TYPE_LINK,
                    'icon' => $item->icon ?? '',
                    'text' => $item->text,
                    'external' => $item->external ?? false,
                    'colored' => $item->colored ?? false,
                ];
            } elseif (isset($item->type) && $item->type == self::TYPE_PRODUCT) {
                $item->model->active = "";

                // Filter state
                if (isset($_GET['active']) && !empty($_GET['active'])) {
                    $item->model->active = $_GET['active'];
                }
                $itemsCount = $item->model->search()->totalItemCount;

                // Set number of page
                App()->user->setState('pageSize', $item->limit);
                $this->limit = $item->limit;

                $surveys = $item->model->search()->getData();
                foreach ($surveys as $survey) {
                    $boxes[] = [
                        'survey' => $survey,
                        'type' => self::TYPE_PRODUCT,
                        'external' => $item->external ?? false,
                        'state' => $survey->getState(),
                        'buttons' => $survey->getButtons(),
                        'link' => Yii::app()->createUrl(
                                'surveyAdministration/view/',
                                ['iSurveyID' => $survey->sid]
                            ),
                    ];
                }
            }
        }

        $createSurveyBox = [];
        $createSurveyGroupBox = [];
        if (Permission::model()->hasGlobalPermission('surveys', 'create')) {
            $createSurveyBox = $this->getCreateSurveyBox();
        }
        if (Permission::model()->hasGlobalPermission('surveysgroups', 'create')) {
            $createSurveyGroupBox = $this->getCreateSurveyGroupBox();
        }

        switch (Survey::model()->count()) {
            case 0:
                if (!empty($createSurveyBox) ) {
                    $boxes[] = $createSurveyBox;
                }
                if (!empty($createSurveyGroupBox) ) {
                    $boxes[] = $createSurveyGroupBox;
                }
                $boxes = array_merge($boxes, $this->getPlaceholders(2 ));
                break;
            case 1:
                if (!empty($createSurveyGroupBox) ) {
                    $boxes[] = $createSurveyGroupBox;
                }
                break;
            default:
        }

        $this->render('boxes', [
            'items' => $boxes,
            'limit' => $this->limit,
            'itemsCount' => $itemsCount
        ]);
    }

    /**
     * Function that returns the create survey box
     * @return array
     */
    private function getCreateSurveyBox()
    {
        return [
            'type' => self::TYPE_LINK,
            'link' => App()->createUrl('/surveyAdministration/newSurvey/'),
            'text' => gT('Create survey'),
            'icon' => 'ri-add-line',
            'colored' => true,
            'external' => false
        ];
    }

    /**
     * Function that returns the create survey group box
     *
     * @return array
     */
    private function getCreateSurveyGroupBox()
    {
        return [
            'type' => self::TYPE_LINK,
            'link' => App()->createUrl('/admin/surveysgroups/sa/create/'),
            'text' => gT('Create survey group'),
            'icon' => 'ri-add-line',
            'colored' => false,
            'external' => false
        ];
    }


    /**
     * Function that returns placeholders for the boxes
     *
     * @param int $amount
     * @return array
     */
    private function getPlaceholders($amount)
    {
        $placeholders = [];
        for($i = 0; $i < $amount; $i++){
            $placeholders[] = [
                'type' => self::TYPE_PLACEHOLDER,
                'link' => '#',
                'text' => '',
                'icon' => '',
                'colored' => false,
                'external' => false
            ];
        }
        return $placeholders;
    }
}
