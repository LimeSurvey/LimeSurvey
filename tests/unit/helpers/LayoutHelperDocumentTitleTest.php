<?php

namespace ls\tests\unit\helpers;

use LayoutHelper;
use ls\tests\TestBaseClass;

/**
 * Test the document title (browser page title) of admin pages.
 */
class LayoutHelperDocumentTitleTest extends TestBaseClass
{
    /**
     * The topbar title is used first.
     */
    public function testTopbarTitle()
    {
        $title = LayoutHelper::getDocumentTitle([
            'topbar' => ['title' => '<b>Global settings</b>'],
            'pageTitle' => 'Other title',
        ]);
        $this->assertSame('Global settings', $title);
    }

    /**
     * Pages without topbar or title bar title (e.g. the dashboard) use the page title.
     */
    public function testPageTitleWithoutTopbarAndTitleBar()
    {
        $title = LayoutHelper::getDocumentTitle(['pageTitle' => 'Dashboard']);
        $this->assertSame('Dashboard', $title);
    }

    /**
     * Pages without any title get none (only the site name is shown).
     */
    public function testNoTitle()
    {
        $this->assertNull(LayoutHelper::getDocumentTitle([]));
        $this->assertNull(LayoutHelper::getDocumentTitle(['pageTitle' => '']));
    }

    /**
     * The subaction is put in front of the survey title of the title bar.
     */
    public function testSubactionBeforeSurveyTitle()
    {
        $title = LayoutHelper::getDocumentTitle([
            'title_bar' => ['title' => 'My survey (ID:123456)'],
            'subaction' => 'Survey theme options',
            'pageTitle' => 'Other title',
        ]);
        $this->assertSame('Survey theme options - My survey (ID:123456)', $title);
    }
}
