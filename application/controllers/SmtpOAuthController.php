<?php

/**
 * @class SmtpOAuthController
 */
class SmtpOAuthController extends LSBaseController
{
    /**
     * It's important to have the accessRules set (security issue).
     * Only logged in users should have access to actions. All other permissions
     * should be checked in the action itself.
     *
     * @return array
     */
    public function accessRules()
    {
        return [
            [
                'allow',
                'actions' => [],
                'users'   => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => ['prepareRefreshTokenRequest'],
                'users'   => ['@'], //only login users
            ],
            ['deny'], //always deny all actions not mentioned above
        ];
    }


    /**
     * Displays the view with Get Token button
     * @param string $plugin
     * @return void
     */
    public function actionPrepareRefreshTokenRequest($plugin)
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->redirect(App()->createUrl("/admin"));
        }

        // Dispatch the plugin event to get needed details for the view,
        // like the size of the auth window.
        $event = new PluginEvent('beforePrepareRedirectToAuthPage', $this);
        Yii::app()->getPluginManager()->dispatchEvent($event, $plugin);
        $width = $event->get('width');
        $height = $event->get('height');
        $data['width'] = $width;
        $data['height'] = $height;

        $this->renderPartial('/admin/smtpoauth/redirectToAuth', $data);
    }

}
