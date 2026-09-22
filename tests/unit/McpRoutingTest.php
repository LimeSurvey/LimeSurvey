<?php

namespace ls\tests;

use CHttpRequest;
use CUrlManager;

class McpRoutingTest extends TestBaseClass
{
    /**
     * The public MCP URL resolves to the MCP controller index action.
     */
    public function testMcpRouteResolvesToMcpControllerIndexAction()
    {
        $request = $this->createMock(CHttpRequest::class);
        $request->method('getPathInfo')->willReturn('mcp');

        $urlManager = $this->createUrlManager();

        $this->assertSame('mcp/index', $urlManager->parseUrl($request));
    }

    /**
     * MCP sub-routes are not mapped to the MCP entry point.
     */
    public function testMcpSubRouteIsNotMappedToMcpIndex()
    {
        $request = $this->createMock(CHttpRequest::class);
        $request->method('getPathInfo')->willReturn('mcp/tools');

        $urlManager = $this->createUrlManager();

        $this->assertSame('mcp/tools', $urlManager->parseUrl($request));
    }

    private function createUrlManager(): CUrlManager
    {
        $urlManager = new CUrlManager();
        $urlManager->urlFormat = CUrlManager::PATH_FORMAT;
        $urlManager->rules = require APPPATH . 'config/routes.php';
        $urlManager->init();

        return $urlManager;
    }
}
