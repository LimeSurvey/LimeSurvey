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
        \Yii::import('application.helpers.expressions.em_manager_helper', true);

        // Emcache is only used from frontend, or when SurveyController is set.
        $sc = new \SurveyController('noid');
        \Yii::app()->setController($sc);

        \EmCacheHelper::init(['sid' => 1, 'active' => 'Y']);

        if (get_class(\Yii::app()->emcache) === 'CDummyCache') {
            \Yii::app()->setComponent('emcache', new \CFileCache());
        }

        if (!\EmCacheHelper::useCache()) {
            echo 'emcache is not set to use';
            exit(1);
        }

    }

    /**
     * Flush all when done with tests.
     */
    public static function teardownAfterClass()
    {
        \Yii::app()->setComponent('emcache', new \CDummyCache());
    }

    /**
     * Always flush everything before every test.
     */
    public function setup()
    {
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
        try {
            \EmCacheHelper::init(['sid' => 1, 'active' => 'Y']);
            $this->assertTrue(true);
        } catch (\InvalidArgumentException $ex) {
            $this->assertTrue(false);
        }
    }

    /**
     * Test basic set and get.
     */
    public function testBasic()
    {
        \EmCacheHelper::init(['sid' => 1, 'active' => 'Y']);
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
        \EmCacheHelper::init(['sid' => 1, 'active' => 'Y']);
        \EmCacheHelper::set('somekey', 'value');

        \EmCacheHelper::flush();

        /** @var string */
        $value = \EmCacheHelper::get('somekey');

        $this->assertEquals(false, $value);
    }

    /**
     * Init twice with same sid.
     */
    public function testDoubleInit()
    {
        \EmCacheHelper::init(['sid' => 3, 'active' => 'Y']);
        \EmCacheHelper::set('somekey', 'value');

        \EmCacheHelper::init(['sid' => 3, 'active' => 'Y']);
        \EmCacheHelper::set('somekey', 'other_value');

        /** @var string */
        $value = \EmCacheHelper::get('somekey');

        $this->assertEquals('other_value', $value);
    }

    /**
     * Test mutliple surveys.
     * @todo Does not work with current emcache keyPrefix code.
     */
    public function testMultipleSurveys()
    {
        $this->markTestSkipped();

        \EmCacheHelper::init(['sid' => 4, 'active' => 'Y']);
        \EmCacheHelper::set('somekey', 'value');

        \EmCacheHelper::init(['sid' => 5, 'active' => 'Y']);
        \EmCacheHelper::set('somekey', 'another_value');

        \EmCacheHelper::init(['sid' => 4, 'active' => 'Y']);

        // This should not flush cache sid 5.
        \EmCacheHelper::flush();

        $value = \EmCacheHelper::get('somekey');
        $this->assertEquals(false, $value);

        \EmCacheHelper::init(['sid' => 5, 'active' => 'Y']);

        /** @var string */
        $value = \EmCacheHelper::get('somekey');
        $this->assertEquals('another_value', $value);
    }
}
