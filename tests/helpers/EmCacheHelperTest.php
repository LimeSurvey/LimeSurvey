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
     * 
     */
    public function testInit()
    {
        \EmCacheHelper::init([1, 2, 3]);
    }
}
