<?php

/**
 * URL rule serving every administration controller under the "admin" URL prefix
 *
 * A controller is an administration controller when its class implements AdminControllerInterface.
 * The rule is registered first in application/config/routes.php, so it wins over the
 * "admin/<action>/sa/<sa>" rules of the legacy AdminController, whose own URLs are left untouched.
 *
 * URL creation: "surveyAdministration/view" + params gives "admin/surveyAdministration/view?surveyid=1",
 * parameters are added as query string like the default rule did for these controllers before.
 * URL parsing: "admin/surveyAdministration/view/surveyid/1" gives the route "surveyAdministration/view"
 * with surveyid in $_GET, so hand-written path style URLs keep working.
 *
 * The rule only acts in path URL format, there is no URL path to protect in GET format.
 */
class LSAdminUrlRule extends CBaseUrlRule
{
    /** @var string The URL prefix shared by all administration controllers */
    const PREFIX = 'admin';

    /** @var array<string,bool> Per request cache: controller id => is an administration controller */
    private static $adminControllers = [];

    /**
     * @inheritdoc
     * Prefix the route of an administration controller with "admin/", parameters go to the query string
     */
    public function createUrl($manager, $route, $params, $ampersand)
    {
        if ($manager->getUrlFormat() !== CUrlManager::PATH_FORMAT) {
            return false;
        }
        $controllerId = explode('/', $route, 2)[0];
        if (!self::isAdminController($controllerId)) {
            return false;
        }
        $url = self::PREFIX . '/' . $route . $manager->urlSuffix;
        if (!empty($params)) {
            $url .= '?' . $manager->createPathInfo($params, '=', $ampersand);
        }
        return $url;
    }

    /**
     * @inheritdoc
     * Strip the "admin/" prefix of an administration controller URL, extra path segments become GET parameters
     */
    public function parseUrl($manager, $request, $pathInfo, $rawPathInfo)
    {
        if (!preg_match('~^' . self::PREFIX . '/(\w+)(?:/(\w+))?(?:/(.+))?$~', $pathInfo, $matches)) {
            return false;
        }
        $controllerId = $matches[1];
        if (!self::isAdminController($controllerId)) {
            return false;
        }
        $route = $controllerId;
        if (isset($matches[2]) && $matches[2] !== '') {
            $route .= '/' . $matches[2];
        }
        if (isset($matches[3]) && $matches[3] !== '') {
            $manager->parsePathInfo($matches[3]);
        }
        return $route;
    }

    /**
     * Whether the controller with this id is an administration controller
     *
     * Resolves the controller class the same way CWebApplication::createController() does
     * (ucfirst(id) + "Controller" in the controller path) and checks it for AdminControllerInterface.
     * The id "admin" is never prefixed: the legacy AdminController already lives under the prefix.
     *
     * @param string $controllerId Controller id as used in routes, e.g. "surveyAdministration"
     * @return bool
     */
    public static function isAdminController($controllerId)
    {
        if (isset(self::$adminControllers[$controllerId])) {
            return self::$adminControllers[$controllerId];
        }
        $isAdminController = false;
        if ($controllerId !== self::PREFIX && preg_match('/^\w+$/', $controllerId)) {
            $className = ucfirst($controllerId) . 'Controller';
            $classFile = Yii::app()->getControllerPath() . DIRECTORY_SEPARATOR . $className . '.php';
            if (is_file($classFile)) {
                if (!class_exists($className, false)) {
                    require_once $classFile;
                }
                $isAdminController = class_exists($className, false)
                    && is_subclass_of($className, 'AdminControllerInterface');
            }
        }
        self::$adminControllers[$controllerId] = $isAdminController;
        return $isAdminController;
    }

    /**
     * Remove the prefix from a route that carries it although the prefix belongs to the URL, not to the route
     *
     * Lets hard-coded prefixed routes work in GET URL format (?r=admin/ajaxAlert/getAlertWidget) and
     * createUrl('admin/surveyAdministration/view') in either format, so one spelling works everywhere.
     * Routes of the legacy AdminController (admin/survey/sa/view) are not touched.
     *
     * @param string $route
     * @return string
     */
    public static function stripPrefix($route)
    {
        $route = ltrim((string) $route, '/');
        if (
            preg_match('~^' . self::PREFIX . '/(\w+)(?:/|$)~', $route, $matches)
            && self::isAdminController($matches[1])
        ) {
            return substr($route, strlen(self::PREFIX) + 1);
        }
        return $route;
    }

    /**
     * Whether a request path already carries the administration prefix
     *
     * @param string $pathInfo Path info of the request, without leading slash
     * @return bool
     */
    public static function hasPrefix($pathInfo)
    {
        return $pathInfo === self::PREFIX || strpos($pathInfo, self::PREFIX . '/') === 0;
    }
}
