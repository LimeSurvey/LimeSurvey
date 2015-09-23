<?php
/**
 * Ideally this file should not be used.
 * Instead the webservers' rootdir should be set to public.
 * This is a workaround for people who:
 * - Cannot configure their server
 * - Are stuck on preconfigured hosting platforms.
 */
    $_SERVER['SCRIPT_NAME'] = 'public/index.php';
    include __DIR__ . '/protected/entry.php';