<?php

/**
 * Marker interface for administration controllers
 *
 * Every controller that implements this interface is served under the "admin" URL prefix:
 * createUrl('surveyAdministration/view') gives /index.php/admin/surveyAdministration/view and
 * a request without the prefix is permanently redirected (GET/HEAD) or refused (404).
 * This keeps the whole administration under one path, so web server authentication
 * (see the Authwebserver plugin) only has to protect /index.php/admin.
 *
 * Implement it on any controller that belongs to the administration, including
 * third-party controllers dropped into application/controllers. The controller id does not change,
 * so plugins checking the controller id in beforeControllerAction keep working.
 *
 * @see LSAdminUrlRule
 * @see LSYii_Application::enforceAdminUrlPrefix()
 */
interface AdminControllerInterface
{
}
