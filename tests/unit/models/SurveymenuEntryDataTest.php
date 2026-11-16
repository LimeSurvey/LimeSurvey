<?php

namespace ls\tests;

class SurveymenuEntryDataTest extends TestBaseClass
{
    /**
     * Build a SurveymenuEntryData from a fake menu entry.
     * @param string $menuLink
     * @param string $data
     * @return \SurveymenuEntryData
     */
    private function applyData($menuLink, $data)
    {
        $menuEntry = new \SurveymenuEntries();
        $menuEntry->name = 'testentry';
        $menuEntry->menu_link = $menuLink;
        $menuEntry->data = $data;

        $entryData = new \SurveymenuEntryData();
        $entryData->apply($menuEntry);
        return $entryData;
    }

    /**
     * An external link is never loaded with pjax, even if pjaxed is not set or set to true.
     */
    public function testExternalLinkIsNotPjaxed()
    {
        $entryData = $this->applyData('https://www.example.org', '{"render":{"link":{"external":1,"data":{}}}}');
        $this->assertTrue($entryData->linkExternal);
        $this->assertFalse($entryData->pjaxed);

        $entryData = $this->applyData('https://www.example.org', '{"render":{"link":{"external":true,"pjaxed":true}}}');
        $this->assertFalse($entryData->pjaxed);
    }

    /**
     * Internal links are pjaxed by default, unless pjaxed is set to false.
     */
    public function testInternalLinkPjaxSetting()
    {
        $entryData = $this->applyData('admin/index', '{"render":{"link":{"data":{}}}}');
        $this->assertFalse($entryData->linkExternal);
        $this->assertTrue($entryData->pjaxed);

        $entryData = $this->applyData('admin/index', '{"render":{"link":{"pjaxed":false}}}');
        $this->assertFalse($entryData->pjaxed);
    }

    /**
     * A full URL set as external link is used as is.
     */
    public function testExternalFullUrlIsKept()
    {
        $entryData = $this->applyData('https://www.example.org/path', '{"render":{"link":{"external":1,"data":{}}}}');
        $this->assertSame('https://www.example.org/path', $entryData->linkCreator());

        $entryData = $this->applyData('https://www.example.org/path?a=1', '{"render":{"link":{"external":1,"data":{"b":"2"}}}}');
        $this->assertSame('https://www.example.org/path?a=1&b=2', $entryData->linkCreator());
    }
}
