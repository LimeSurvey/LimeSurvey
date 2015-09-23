<?php
/**
 * Ideally this file should not be used.
 * Instead the webservers' rootdir should be set to public.
 * This is a workaround for people who:
 * - Cannot configure their server
 * - Are stuck on preconfigured hosting platforms.
 */
// We do this to not have to depend on $_SERVER, ever.
$webroot = __DIR__;
require __DIR__ . '/protected/entry.php';