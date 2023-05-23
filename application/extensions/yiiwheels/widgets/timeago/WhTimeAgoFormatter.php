<?php
/**
 * WhTimeAgoFormatter class
 *
 * @author Alex G <gubarev.alex@gmail.com>
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.timeago
 */
class WhTimeAgoFormatter extends CFormatter
{
    /**
     * @var string name of locale
     */
    public $locale;

    /**
     * @var boolean allow future prefix in 'timeago' output
     */
    public $allowFuture = true;

    /**
     * @var array holds the locale data
     */
    private $data;


    /**
     * Component initialization
     */
    public function init()
    {
        if (empty($this->locale)) {
            $this->locale = Yii::app()->language;
        }
        $this->setLocale($this->locale);
        parent::init();
    }

    /**
     * Includes file with locale-specific data array. When locale isnt exists used default 'en' locale
     * @param string $locale locale name (like 'ru', 'en_short' etc.)
     */
    private function setLocale($locale)
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR .
            'assets' . DIRECTORY_SEPARATOR .
            'php' . DIRECTORY_SEPARATOR .
            'locale' . DIRECTORY_SEPARATOR . $locale . '.php';

        if (!file_exists($path)) {
            $this->locale = 'en';
            $path         = __DIR__ . DIRECTORY_SEPARATOR .
                'assets' . DIRECTORY_SEPARATOR .
                'php' . DIRECTORY_SEPARATOR .
                'locale' . DIRECTORY_SEPARATOR . $this->locale . '.php';
        }
        $this->data = require_once($path);
    }

    /**
     * Formats value in timeago formatted string
     * @param mixed $value timestamp, DateTime or date-formatted string
     * @return string timeago formatted string
     */
    public function formatTimeago($value)
    {
        if ($value instanceof DateTime) {
            $value = date_timestamp_get($value);

        } else if (!is_numeric($value) && is_string($value)) {
            $value = strtotime($value);
        }

        return $this->inWords((time() - $value));
    }

    /**
     * Converts time delta to timeago formatted string
     * @param integer $seconds time delta in seconds
     * @return string timeago formatted string
     */
    public function inWords($seconds)
    {
        $prefix = $this->data['prefixAgo'];
        $suffix = $this->data['suffixAgo'];
        if ($this->allowFuture && $seconds < 0) {
            $prefix = $this->data['prefixFromNow'];
            $suffix = $this->data['suffixFromNow'];
        }

        $seconds = abs($seconds);

        $minutes = $seconds / 60;
        $hours   = $minutes / 60;
        $days    = $hours / 24;
        $years   = $days / 365;

        $separator = $this->data['wordSeparator'] ?? " ";

        $wordsConds = array(
            $seconds < 45,
            $seconds < 90,
            $minutes < 45,
            $minutes < 90,
            $hours < 24,
            $hours < 42,
            $days < 30,
            $days < 45,
            $days < 365,
            $years < 1.5,
            true
        );

        $wordResults = array(
            array('seconds', round($seconds)),
            array('minute', 1),
            array('minutes', round($minutes)),
            array('hour', 1),
            array('hours', round($hours)),
            array('day', 1),
            array('days', round($days)),
            array('month', 1),
            array('months', round($days / 30)),
            array('year', 1),
            array('years', round($years))
        );

        for ($i = 0; $i < $count = count($wordsConds); ++$i) {
            if ($wordsConds[$i]) {
                $key    = $wordResults[$i][0];
                $number = $wordResults[$i][1];
                if (is_array($this->data[$key]) && is_callable($this->data['rules'])) {
                    $n       = call_user_func($this->data['rules'], $wordResults[$i][1]);
                    $message = $this->data[$key][$n];
                } else {
                    $message = $this->data[$key];
                }
                return trim(implode($separator, array($prefix, preg_replace('/%d/i', $number, (string) $message), $suffix)));
            }
        }
    }

}