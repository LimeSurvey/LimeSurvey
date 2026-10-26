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
                'actions' => ['receiveOAuthResponse'],
                'users'   => ['*'], //everybody
            ],
            [
                'allow',
                'actions' => ['prepareRefreshTokenRequest', 'launchRefreshTokenRequest'],
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
            Yii::app()->user->setFlash('error', gT("Access denied!"));
            $this->redirect(Yii::app()->createUrl("/admin"));
        }

        $pluginModel = Plugin::model()->findByAttributes(['name' => $plugin]);
        if (empty($pluginModel) || !$pluginModel->active) {
            Yii::app()->user->setFlash('error', gT("Invalid plugin"));
            $this->redirect(Yii::app()->createUrl("/admin"));
        }

        // Dispatch the plugin event to get details needed for the view,
        // like the size of the auth window.
        $event = new PluginEvent('beforePrepareRedirectToAuthPage', $this);
        Yii::app()->getPluginManager()->dispatchEvent($event, $plugin);
        $data['width'] = $event->get('width');
        $data['height'] = $event->get('height');
        $data['providerName'] = $event->get('providerName', $plugin);
        $data['topbar']['title'] = gT('Get OAuth 2.0 token for SMTP authentication');
        $data['providerUrl'] = $this->createUrl('smtpOAuth/launchRefreshTokenRequest', ['plugin' => $plugin]);

        $pluginEventContent = $event->getContent($plugin);
        $description = null;
        if ($pluginEventContent->hasContent()) {
            $description = CHtml::tag(
                'div',
                [
                    'id' => $pluginEventContent->getCssId(),
                    'class' => $pluginEventContent->getCssClass()
                ],
                $pluginEventContent->getContent()
            );
        }
        $data['description'] = $description;

        $data['redirectUrl'] = $this->createUrl(
            '/admin/pluginmanager',
            [
                'sa' => 'configure',
                'id' => $pluginModel->id
            ]
        );

        $this->aData = $data;

        $this->render('redirectToAuth', $data);
    }

    public function actionLaunchRefreshTokenRequest($plugin)
    {
        if (!Permission::model()->hasGlobalPermission('settings', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied!"));
            $this->redirect(Yii::app()->createUrl("/admin"));
        }

        // Dispatch the plugin event to get the redirect URL
        $event = new PluginEvent('beforeRedirectToAuthPage', $this);
        Yii::app()->getPluginManager()->dispatchEvent($event, $plugin);
        $authUrl = $event->get('authUrl');

        $this->setOAuthState($plugin, $event->get('state'));

        header('Location: ' . $authUrl);
        exit;
    }

    /**
     * Receive the response from the OAuth provider
     * @return void
     */
    public function actionReceiveOAuthResponse()
    {
        // Make sure the request includes the required data
        $code = Yii::app()->request->getParam('code');
        $state = Yii::app()->request->getParam('state');
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
        $this->clearOAuthState($plugin);

        Yii::app()->user->setFlash('success', gT('The OAuth 2.0 token was successfully retrieved.'));

        // Render the HTML that will be displayed in the popup window
        // The HTML will close the window and cause the page to reload
        $this->renderPartial('/smtpOAuth/responseReceived', []);
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

    /**
     * Set the OAuth state for the given plugin.
     * @param string $plugin
     * @param string $state
     */
    protected function setOAuthState($plugin, $state)
    {
        $smtpOAuthStates = Yii::app()->session['smtpOAuthStates'] ?? [];
        $smtpOAuthStates[$plugin] = $state;
        Yii::app()->session['smtpOAuthStates'] = $smtpOAuthStates;
    }

    protected function clearOAuthState($plugin)
    {
        $smtpOAuthStates = Yii::app()->session['smtpOAuthStates'] ?? [];
        unset($smtpOAuthStates[$plugin]);
        Yii::app()->session['smtpOAuthStates'] = $smtpOAuthStates;
    }
}
