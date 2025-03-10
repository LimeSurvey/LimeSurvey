<?php

namespace ls\mersenne;

/**
 * Set seed for this response
 * If there is no seed, create a new one
 * Also inits the twister.
 * @param int $surveyid
 * @return void
 */
function setSeed($surveyid)
{
    /* In started survey : get seed from response table */
    if (isset($_SESSION['responses_' . $surveyid]['srid'])) {
        $oResponse = \Response::model($surveyid)->findByPk($_SESSION['responses_' . $surveyid]['srid']);
        $seed = $oResponse->seed;
        /* fix empty seed, this allow broken seed (not number) */
        if (empty($seed)) {
            $seed = mt_rand();
            $oResponse->seed = $seed;
            $oResponse->save();
        }
    } else {
        $seed = mt_rand();
        /* On activated (but not started) survey : set seed in startingValues */
        if (\Survey::model()->findByPk($surveyid)->getIsActive()) {
            $table = \Yii::app()->db->schema->getTable('{{responses_' . $surveyid . '}}');
            if (isset($table->columns['seed'])) {
                $_SESSION['responses_' . $surveyid]['startingValues']['seed'] = $seed;
            }
        }
    }
    MersenneTwister::init($seed);
}

/**
 * Shuffle an array using MersenneTwister
 * Argument NOT called by reference!
 * @param array $arr
 * @return array
 */
function shuffle(array $arr)
{
    $mt = MersenneTwister::getInstance();
    return $mt->shuffle($arr);
}

/**
 * Custom random algorithm to get consistent behaviour between PHP versions.
 *
 * Copied from: http://www.dr-chuck.com/csev-blog/2015/09/a-mersenne_twister-implementation-in-php/
 */
class MersenneTwister
{
    private $state = array();
    private $index = 0;

    /**
     * Singleton variable
     * @var MersenneTwister
     */
    private static $instance = null;

    /**
     * @param int $seed
     * @return void
     */
    public static function init($seed)
    {
        self::$instance = new MersenneTwister($seed);
    }

    /**
     * @return MersenneTwister
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            throw new \Exception('Must init MersenneTwister before use. Should be done in randomizationGroupsAndQuestions.');
        }

        return self::$instance;
    }

    /**
     * Shuffle with seed
     * @param array $arr
     * @param $seed
     * @return array
     */
    public function shuffle($arr)
    {
        $mt = self::$instance;
        $new = $arr;
        for ($i = count($new) - 1; $i > 0; $i--) {
            $j = $mt->getNext(0, $i);
            $tmp = $new[$i];
            $new[$i] = $new[$j];
            $new[$j] = $tmp;
        }
        return $new;
    }


    /**
     * @param integer $seed
     */
    public function __construct($seed = null)
    {
        if ($seed === null) {
                    $seed = mt_rand();
        }

        $this->setSeed($seed);
    }

    /**
     * @param integer $seed
     */
    public function setSeed($seed)
    {
        $this->state[0] = $seed & 0xffffffff;

        for ($i = 1; $i < 624; $i++) {
            $this->state[$i] = (((0x6c078965 * ($this->state[$i - 1] ^ ($this->state[$i - 1] >> 30))) + $i)) & 0xffffffff;
        }

        $this->index = 0;
    }

    private function generateTwister()
    {
        for ($i = 0; $i < 624; $i++) {
            $y = (($this->state[$i] & 0x1) + ($this->state[$i] & 0x7fffffff)) & 0xffffffff;
            $this->state[$i] = ($this->state[($i + 397) % 624] ^ ($y >> 1)) & 0xffffffff;

            if (($y % 2) == 1) {
                $this->state[$i] = ($this->state[$i] ^ 0x9908b0df) & 0xffffffff;
            }
        }
    }

    /**
     * @param integer $min
     * @param integer $max
     */
    public function getNext($min = null, $max = null)
    {
        if (($min === null && $max !== null) || ($min !== null && $max === null)) {
            throw new \Exception('Invalid arguments');
        }

        if ($this->index === 0) {
            $this->generateTwister();
        }

        $y = $this->state[$this->index];
        $y = ($y ^ ($y >> 11)) & 0xffffffff;
        $y = ($y ^ (($y << 7) & 0x9d2c5680)) & 0xffffffff;
        $y = ($y ^ (($y << 15) & 0xefc60000)) & 0xffffffff;
        $y = ($y ^ ($y >> 18)) & 0xffffffff;

        $this->index = ($this->index + 1) % 624;

        if ($min === null && $max === null) {
                    return $y;
        }

        $range = abs($max - $min);

        return min($min, $max) + ($y % ($range + 1));
    }
}
