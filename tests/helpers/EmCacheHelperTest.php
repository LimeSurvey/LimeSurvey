<?php

namespace ls\tests;

/**
 * Test the emcache.
 *
 * @since 2019-05-23
 * @group emcache
 */
class EmCacheHelperTest extends TestBaseClass
{
    /**
     * Setup.
     */
    public static function setUpBeforeClass()
    {
        \Yii::import('application.helpers.expressions.em_cache_exception', true);
        \Yii::import('application.helpers.expressions.em_cache_helper', true);


        if (!\EmCacheHelper::useCache()) {
            echo 'emcache is not set to use';
            exit(1);
        }

        if (get_class(\Yii::app()->emcache) === 'CDummyCache') {
            \Yii::app()->setComponent('emcache', new \CFileCache());
        }
    }

    /**
     * Flush all when done with tests.
     */
    public static function teardownAfterClass()
    {
        \EmCacheHelper::flushAll();
        \Yii::app()->setComponent('emcache', new \CDummyCache());
    }

    /**
     * Always flush everything before every test.
     */
    public function setup()
    {
        \EmCacheHelper::flushAll();
        \EmCacheHelper::clearInit();
    }

    /**
     * Should throw an exception.
     */
    public function testEmptyInit()
    {
        try {
            \EmCacheHelper::init(null);
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
    }

    /**
     * Test wrong init (no sid).
     */
    public function testWrongInit()
    {
        try {
            \EmCacheHelper::init([1, 2, 3]);
            $this->assertTrue(false);
        } catch (\InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
    }

    /**
     * Test correct init.
     */
    public function testCorrectInit()
    {
        \EmCacheHelper::init(['sid' => 1]);
    }

    /**
     * Test basic set and get.
     */
    public function testBasic()
    {
        \EmCacheHelper::init(['sid' => 1]);
        \EmCacheHelper::set('somekey', 'value');

        /** @var string */
        $value = \EmCacheHelper::get('somekey');

        $this->assertEquals('value', $value);
    }

    /**
     * Test flush.
     */
    public function testFlush()
    {
        \EmCacheHelper::init(['sid' => 1]);
        \EmCacheHelper::set('somekey', 'value');

        \EmCacheHelper::flush();

        /** @var string */
        $value = \EmCacheHelper::get('somekey');

        $this->assertEquals(false, $value);
    }

    /**
     * Test mutliple surveys.
     */
    public function testMultipleSurveys()
    {
        \EmCacheHelper::init(['sid' => 1]);
        \EmCacheHelper::set('somekey', 'value');

        \EmCacheHelper::init(['sid' => 2]);
        \EmCacheHelper::set('somekey', 'another_value');

        \EmCacheHelper::init(['sid' => 1]);
        // This should not flush cache sid 2.
        \EmCacheHelper::flush();

        $value = \EmCacheHelper::get('somekey');
        $this->assertEquals(false, $value);

        \EmCacheHelper::init(['sid' => 2]);

        /** @var string */
        $value = \EmCacheHelper::get('somekey');
        $this->assertEquals('another_value', $value);
    }
}
