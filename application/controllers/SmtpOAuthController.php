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
            $this->redirect(Yii::app()->createUrl("/admin"));
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

    /**
     * Receive the response from the OAuth provider
     * @return void
     */
    public function actionReceiveOAuthResponse()
    {
        // Check if the email method is set to OAuth2
        if (LimeMailer::MethodOAuth2Smtp !== Yii::app()->getConfig('emailmethod')) {
            throw new CHttpException(400);
        }

        // Make sure the request includes the required data
        $code = Yii::app()->request->getPost('code');
        $state = Yii::app()->request->getPost('state');
        if (empty($code) || empty($state)) {
            throw new CHttpException(400);
        }

        // Find the plugin class matching the given OAuth state
        $plugin = $this->getPluginClassByOAuthState($state);

        // If no plugin was found, the state is invalid
        if (empty($plugin)) {
            throw new CHttpException(400);
        }

        $event = new PluginEvent('afterReceiveOAuthResponse', $this);
        $event->set('code', $code);
        $event->set('state', $state);
        Yii::app()->getPluginManager()->dispatchEvent($event, $plugin);

        // Remove the state from the session
        unset(Yii::app()->session['smtpOAuthStates'][$plugin]);

        // Render the HTML that will be displayed in the popup window
        // The HTML will close the window and cause the page to reload
        $this->renderPartial('/admin/smtpoauth/ResponseReceived', []);
    }

    /**
     * Find the plugin class matching the given OAuth state.
     * @param string $state
     * @return string|null
     */
    protected function getPluginClassByOAuthState($state)
    {
        $pluginsWithOAuthState = Yii::app()->session['smtpOAuthStates'] ?? [];
        return array_search($state, $pluginsWithOAuthState, true);
    }
}
