<?php

class BoxesWidget extends CWidget
{
    const TYPE_PRODUCT = 0;
    const TYPE_PRODUCT_GROUP = 1;
    const TYPE_LINK = 2;
    public $items = [];
    public $limit = 3;
//    public $offset = 2;
    public $boxesbyrow = 4;

    public function run()
    {
        $boxes = [];
        foreach ($this->items as $item) {
            $item = (object)$item;

            if ($item->type == self::TYPE_LINK) {
                $boxes[] = [
                    'link' => $item->link,
                    'type' => self::TYPE_LINK,
                    'icon' => $item->icon ?? '',
                    'text' => $item->text,
                    'external' => $item->external ?? false,
                    'color' => $item->color ?? '',
                ];
            } elseif ($item->type == self::TYPE_PRODUCT) {
                 $surveys = $item->model->search(
                     ['pageSize' => $item->limit]
                 )->getData();

                foreach ($surveys as $survey) {
                    list($icon, $iconAlter) = $this->getRunning($survey);
                    $boxes[] = [
                        'survey' => $survey,
                        'type' => self::TYPE_PRODUCT,
                        'external' => $item->external ?? false,
                        'icon' => $icon,
                        'iconAlter' => $iconAlter,
                        'buttons' => $survey->getButtons(),
                        'link' => App()->createUrl('/surveyAdministration/view/surveyid/' . $survey->sid),
                    ];
                }
            }
        }
        $this->render('boxes', [
            'items' => $boxes,
            'boxesbyrow' => $this->boxesbyrow
        ]);
    }


    public function getRunning($survey)
    {

        // If the survey is not active, no date test is needed
        if ($survey->active === 'N') {
            $running = ['ri-stop-fill text-secondary me-1', gT('Inactive')]; // Inactive
        } elseif (!empty($survey->expires) || !empty($survey->startdate)) {
            // Create DateTime for now, stop and start for date comparison
            $oNow = self::shiftedDateTime("now");
            $oStop = self::shiftedDateTime($survey->expires);
            $oStart = self::shiftedDateTime($survey->startdate);

            $bExpired = (!is_null($oStop) && $oStop < $oNow);
            $bWillRun = (!is_null($oStart) && $oStart > $oNow);

            $sStop = !is_null($oStop) ? convertToGlobalSettingFormat($oStop->format('Y-m-d H:i:s')) : "";
            $sStart = !is_null($oStart) ? convertToGlobalSettingFormat($oStart->format('Y-m-d H:i:s')) : "";

            // Icon generaton (for CGridView)
            $sIconRunNoEx = ['ri-play-fill text-primary me-1' , gT('End: Never')]; // Never
            $sIconRunning = ['ri-play-fill text-primary', sprintf(gT('End: %s'), $sStop)];
            $sIconExpired = ['ri-skip-forward-fill text-secondary', sprintf(gT('Expired: %s'), $sStop)];
            $sIconFuture  = ['ri-time-line text-secondary', sprintf(gT('Start: %s'), $sStart)];

            // Icon parsing
            if ($bExpired || $bWillRun) {
                // Expire prior to will start
                $running = ($bExpired) ? $sIconExpired : $sIconFuture;
            } else {
                if ($sStop === "") {
                    $running = $sIconRunNoEx;
                } else {
                    $running = $sIconRunning;
                }
            }
        } else {
            // If it's active, and doesn't have expire date, it's running
            $running = ['ri-play-fill text-primary', gT('Active')];
        }

        return $running;
    }

    private static function shiftedDateTime($datetime)
    {
        if (is_string($datetime) && strtotime($datetime)) {
            $datetime = dateShift($datetime, "Y-m-d H:i:s", strval(Yii::app()->getConfig('timeadjust')));
            return new DateTime($datetime);
        }
        return null;
    }
}
