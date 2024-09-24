<?php

namespace LimeSurvey\Yii\Application;

use Yii;
use CWebApplication;
use CHttpException;
use LSUserException;

/**
 * Application error handling logic
 */
class AppErrorHandler
{
    /**
     * @see http://www.yiiframework.com/doc/api/1.1/CApplication#onException-detail
     * Set surveys/error for 404 error
     *
     * @param integer|null $dbVersion
     * @param CExceptionEvent $event
     * @return void
     */
    public function onException($dbVersion, $event)
    {
        if (!Yii::app() instanceof CWebApplication) {
            /* Don't update for CLI */
            return;
        }
        if (defined('PHP_ENV') && PHP_ENV == 'test') {
            // If run from phpunit, die with exception message.
            die($event->exception->getMessage());
        }
        if (!$dbVersion) {
            /* Not installed or DB broken or to old */
            return;
        }
        if ($dbVersion < 200) {
            /* Activate since DBVersion for 2.50 and up (i know it include previous line, but stay clear) */
            return;
        }
        if (Yii::app()->getRequest()->isRestRequest()) {
            $this->handleRestException($event);
        } else {
            $this->handleWebException($event);
        }
    }

    /**
     * @see http://www.yiiframework.com/doc/api/1.1/CApplication#onError-detail
     *
     * @param integer|null $dbVersion
     * @param CErrorEvent $event
     * @return void
     */
    public function onError($dbVersion, $event)
    {
        if (!Yii::app() instanceof CWebApplication) {
            /* Don't update for CLI */
            return;
        }
        if (defined('PHP_ENV') && PHP_ENV == 'test') {
            // If run from phpunit, die with exception message.
            die(
                isset($event->exception)
                ? $event->exception->getMessage()
                : $event->message
            );
        }
        if (!$dbVersion) {
            /* Not installed or DB broken or to old */
            return;
        }
        if ($dbVersion < 200) {
            /* Activate since DBVersion for 2.50 and up (i know it include previous line, but stay clear) */
            return;
        }
        if (Yii::app()->getRequest()->isRestRequest()) {
            $this->handleRestError($event);
        }
    }

    /**
     * Handle web exception
     *
     * @param CExceptionEvent $event
     * @return void
     */
    private function handleWebException($event)
    {
        if (
            Yii::app()->request->isAjaxRequest &&
            $event->exception instanceof CHttpException
        ) {
            $this->ajaxErrorResponse($event->exception);
        } elseif ($event->exception instanceof LSUserException) {
            $this->handleWebFriendlyException($event->exception);
        }

        $statusCode = $event->exception->statusCode ?? null; // Needed ?
        if (Yii::app()->getConfig('debug') > 1) {
            /* Can restrict to admin ? */
            /* debug ro 2 : always send Yii debug even 404 */
            return;
        }
        if (Yii::app()->getConfig('debug') > 0 && $statusCode != '404') {
            /* debug is set and not a 404 : always send Yii debug*/
            return;
        }
        Yii::app()->setComponent('errorHandler', array(
            'errorAction' => 'surveys/error',
        ));
    }

    /**
     * Handles "friendly" exceptions by setting a flash message and redirecting.
     * If the exception doesn't specify a redirect URL, the referrer is used.
     *
     * @param array $error
     * @param LSUserException $exception
     * @return void
     */
    private function handleWebFriendlyException($exception)
    {
        $message = "<p>" . $exception->getMessage() . "</p>" . $exception->getDetailedErrorSummary();
        Yii::app()->setFlashMessage($message, 'error');
        if ($exception->getRedirectUrl() != null) {
            $redirectTo = $exception->getRedirectUrl();
        } else {
            $redirectTo = Yii::app()->request->urlReferrer;
        }
        Yii::app()->request->redirect($redirectTo);
    }

    /**
     * Outputs an exception as JSON.
     *
     * @param CHttpException $exception
     * @return void
     */
    private function ajaxErrorResponse($exception)
    {
        $outputData = [
            'success' => false,
            'message' => $exception->getMessage(),
        ];
        if ($exception instanceof LSUserException) {
            if ($exception->getRedirectUrl() != null) {
                $outputData['redirectTo'] = $exception->getRedirectUrl();
            }
            if ($exception->getNoReload() != null) {
                $outputData['noReload'] = $exception->getNoReload();
            }
            // Add the detailed errors to the message, so simple handlers can just show it.
            $outputData['message'] = "<p>" . $exception->getMessage() . "</p>". $exception->getDetailedErrorSummary();
            // But save the "simpler" message on 'error', and the list of errors on "detailedErrors"
            // so that more complex handlers can decide what to show.
            $outputData['error'] = $exception->getMessage();
            $outputData['detailedErrors'] = $exception->getDetailedErrors();
        }
        header('Content-Type: application/json');
        http_response_code($exception->statusCode);
        echo json_encode($outputData);
        Yii::app()->end();
    }

	/**
	 * Handles uncaught PHP exceptions.
	 *
     * @param CExceptionEvent $event
     * @return void
	 */
    private function handleRestException($event)
	{
        $this->restErrorResponse(
            $event->exception->getCode(),
            $event->exception->getMessage(),
            $event->exception->getFile(),
            $event->exception->getLine()
        );
    }

    /**
	 * Handles PHP execution errors such as warnings, notices.
	 *
	 * @param CErrorEvent
	 */
    private function handleRestError($event)
	{
        $this->restErrorResponse(
            $event->code,
            $event->message,
            $event->file,
            $event->line
        );
    }

    /**
	 * JSON error response.
	 *
	 * @param integer $code the level of the error raised
	 * @param string $message the error message
	 * @param string $file the filename that the error was raised in
	 * @param integer $line the line number the error was raised at
	 */
    private function restErrorResponse($code, $message, $file, $line)
    {
        http_response_code(500);
        $responseData = [
            'error' => [
                'code' => 0,
                'message' => 'Server error',
            ]
        ];
        if (Yii::app()->getConfig('debug') > 1) {
            $responseData['error'] = [
                'code' => $code,
                'message' => $message,
                'file' => $file,
                'line' => $line
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($responseData);
        Yii::app()->end();
    }
}
