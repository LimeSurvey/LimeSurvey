<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*
*/

/**
*
* @package       LimeSurvey
* @subpackage    Backend
*/

/**
*
* This controller performs updates, it is highly ajax oriented
* Methods are only called from JavaScript controller (wich is called from the global_setting view). comfortupdate.js is the first registered script.
*
*
*
* Public methods are written in a chronological way:
*   - First, when the user click on the 'check for updates' button, the plugin buildComfortButtons.js call for getstablebutton() or getbothbuttons() method and inject the HTML inside the li#udapteButtonsContainer in the _checkButtons view
*   - Then, when the user click on one of those buttons, the comfortUpdateNextStep.js plugin will call for the getWelcome() method and inject the HTML inside div#updaterContainer in the _right_container view (all steps will be then injected here)
*   - Then, when the user click on the continue button, the comfortUpdateNextStep.js plugin will call for the step1() method and inject the  the HTML inside div#updaterContainer in the _right_container view
*   - etc. etc.
*
*
*
*  Some steps must be shown out of the chronological process: getNewKey and submitKey. They are at the end of the controller's interface.
*  Some steps must be 'checked again' after the user fixed some errors (such as file permissions).
*  Those steps are/can be diplayed by the plugin displayComfortStep.js. They are called from buttons like :
*
*  <a class='button' href='<?php Yii::app()->createUrl('admin/globalsettings', array('update'=>'methodToCall', 'neededVariable'=>$value));?>'>
*    <span class='ui-button-text'>button text</span>
*  </a>
*
* so they will call an url such as : globalsettings?update=methodToCall&neededVariable=value.
* So the globalsetting controller will render the view as usual, but : the _ajaxVariables view will parse those url datas to some hidden field.
* The comfortupdate.js check the value of the hidden field update, and if the update's one contain a step, it call displayComfortStep.js wich will display the right step instead of the 'check update' buttons.
*
* Most steps are retrieving datas from the comfort update server thanks to the model UpdateForm's methods.
* The server return an answer object, with a property 'result' to tell if the process was succesfull or if it failed. This object contains in general all the necessary datas for the views.
*
*
* Handling errors :
* They are different types of possible errors :
* - Warning message (like : modified files, etc.) : they don't stop the process, they are parsed to the step view, and the view manage how to display them. They can be generated from the ComfortUpdate server ($answer_from_server->result == TRUE ; and something like $answer_from_server->error == message or anything else that the step view manage ), or in the LimeSurvey update controller/model
* - Error while processing a request on the server part : should never happen, but if something goes wrong in the server side (like generating an object from model), the server returns an error object ($answer_from_server->result == FALSE ; $answer_from_server->error == message )
*   Those errors stop the process, and are display in _error view. Very usefull to debug. They are parsed directly to $this->_renderError
* - Error while checking needed datas in the LimeSurvey update controller : the controller always check if it has the needed datas (such as destintion_build, or zip_file), or the state of the key (outdated, etc). For the code to be dryer, the method parse an error string to $this->_renderErrorString($error), wich generate the error object, and then render the error view
*
* @package       LimeSurvey
* @subpackage    Backend
*/
class update extends Survey_Common_Action
{

    /**
     * First function to be called, when comming to admin/update
     *
     */
    public function index()
    {
        if (Yii::app()->getConfig('demoMode')) {
            Yii::app()->setFlashMessage(gT('This function cannot be executed because demo mode is active.'), 'error');
            $this->getController()->redirect(Yii::app()->getController()->createUrl("/admin"));
        }
        $buttons = 1;
        $updateModel = new UpdateForm();
        $serverAnswer = $updateModel->getUpdateInfo($buttons);
        $aData['serverAnswer'] = $serverAnswer;
        $aData['fullpagebar']['update'] = true;
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'comfortupdate/comfortupdate.js');
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'comfortupdate/buildComfortButtons.js');
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'comfortupdate/displayComfortStep.js');

        $this->_renderWrappedTemplate('update', '_updateContainer', $aData);
    }

    public function managekey()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            $buttons = 1;
            $updateModel = new UpdateForm();
            $serverAnswer = $updateModel->getUpdateInfo($buttons);
            $aData['serverAnswer'] = $serverAnswer;
            $aData['fullpagebar']['closebutton']['url'] = 'admin/update';
            $updateKey = $aData['updateKey'] = getGlobalSetting('update_key');

            //$this->controller->renderPartial('//admin/update/updater/welcome/_subscribe', array('serverAnswer' => $serverAnswer),  false, false);
            if (!$updateKey) {
                $aData['fullpagebar']['saveandclosebutton']['form'] = true;
                $this->_renderWrappedTemplate('update/manage/', 'subscribe', $aData);
            } else {
                $aData['updateKeyInfos'] = $updateModel->checkUpdateKeyonServer($updateKey);
                $this->_renderWrappedTemplate('update/manage/', 'manage_key', $aData);
            }
        }
    }

    public function manage_submitkey()
    {
        $buttons = 1;
        $updateModel = new UpdateForm();
        $serverAnswer = $updateModel->getUpdateInfo($buttons);
        $aData['serverAnswer'] = $serverAnswer;
        $aData['fullpagebar']['closebutton']['url'] = 'admin/update';
        $aData['updateKey'] = $updateKey = SettingGlobal::model()->findByPk('update_key');

        if (Permission::model()->hasGlobalPermission('superadmin')) {
            if (Yii::app()->request->getPost('keyid')) {
                // We trim it, just in case user added a space...
                $submittedUpdateKey = trim(Yii::app()->request->getPost('keyid'));

                $updateModel = new UpdateForm();
                $check = $updateModel->checkUpdateKeyonServer($submittedUpdateKey);
                if ($check->result) {
                    // If the key is validated by server, we update the local database with this key
                    $updateKey = $updateModel->setUpdateKey($submittedUpdateKey);
                    Yii::app()->session['flashmessage'] = gT("Your key has been updated and validated! You can now use ComfortUpdate.");
                    // then, we render the what returned the server (views and key infos or error )
                    App()->getController()->redirect(Yii::app()->getController()->createUrl('admin/update/sa/managekey'));
                } else {
                    switch ($check->error) {
                        case 'out_of_updates':
                            $title = "Your update key is out of update !";
                            $message = "you should first renew this key before using it, or try to enter a new one !";
                            $buttons = 1;
                            break;

                        case 'expired':
                            $title = "Your update key has expired!";
                            $message = "you should first renew this key before using it, or try to enter a new one !";
                            $buttons = 1;
                            break;

                        case 'not_found':
                            $title = "Unknown update key !";
                            $message = "Your key is unknown by the update server.";
                            $buttons = 3;
                            break;

                        case 'key_null':
                            $title = "key can't be null !";
                            $message = "";
                            $buttons = 3;
                            break;
                    }

                    App()->setFlashMessage('<strong>'.gT($title).'</strong> '.gT($message), 'error');
                    App()->getController()->redirect(Yii::app()->getController()->createUrl('admin/update/sa/managekey'));
                }

            }
        }
    }

    public function delete_key()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            SettingGlobal::model()->deleteByPk('update_key');
            App()->setFlashMessage('Your update key has been removed');
            App()->getController()->redirect(Yii::app()->getController()->createUrl('admin/update/sa/managekey'));
        }
    }

    /**
     * This function return the update buttons for stable branch
     * @return html the button code
     */
    public function getstablebutton()
    {
        echo $this->_getButtons("1");
    }

    /**
     * This function return the update buttons for all versions
     * @return html the buttons code
     */
    public function getbothbuttons()
    {
        echo $this->_getButtons("1");
    }

    /**
     * This function has a special rendering, because the ComfortUpdate server can choose what it's going to show :
     * the welcome message or the subscribe message or the updater update, etc.
     * The same system is used for the static views (update key, etc.)
     *
     * @return string|null the welcome message
     */
    public function getwelcome()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            // We get the update key in the database. If it's empty, getWelcomeMessage will return subscription
            $updateKey = getGlobalSetting("update_key");
            $updateModel = new UpdateForm();
            $destinationBuild = $_REQUEST['destinationBuild'];
                $welcome = (array) $updateModel->getWelcomeMessage($updateKey, $destinationBuild);
                $welcome['destinationBuild'] = $destinationBuild;
            $welcome = (object) $welcome;

                return $this->_renderWelcome($welcome);
        }
    }

    /**
     * returns the "Checking basic requirements" step
     * @return html the welcome message
     */
    public function checkLocalErrors()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            // We use request rather than post, because this step can be called by url by displayComfortStep.js
            if (isset($_REQUEST['destinationBuild'])) {
                $destinationBuild = $_REQUEST['destinationBuild'];
                $access_token     = $_REQUEST['access_token'];

                $updateModel = new UpdateForm();
                $localChecks = $updateModel->getLocalChecks($destinationBuild);
                $aData['localChecks'] = $localChecks;
                $aData['changelog'] = null;
                $aData['destinationBuild'] = $destinationBuild;
                $aData['access_token'] = $access_token;

                return $this->controller->renderPartial('update/updater/steps/_check_local_errors', $aData, false, false);
            }
            return $this->_renderErrorString("unknown_destination_build");
        }
    }

    /**
     * Display change log
     * @return HTML
     */
    public function changeLog()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {

            // We use request rather than post, because this step can be called by url by displayComfortStep.js
            if (isset($_REQUEST['destinationBuild'])) {
                $destinationBuild = $_REQUEST['destinationBuild'];
                $access_token     = $_REQUEST['access_token'];

                // We get the change log from the ComfortUpdate server
                $updateModel = new UpdateForm();
                $changelog = $updateModel->getChangeLog($destinationBuild);

                if ($changelog->result) {
                    $aData['errors'] = false;
                    $aData['changelogs'] = $changelog;
                    $aData['html_from_server'] = $changelog->html;
                    $aData['destinationBuild'] = $destinationBuild;
                    $aData['access_token'] = $access_token;
                } else {
                    return $this->_renderError($changelog);
                }
                return $this->controller->renderPartial('update/updater/steps/_change_log', $aData, false, false);
            }
            return $this->_renderErrorString("unknown_destination_build");
        }
    }

    /**
     * diaplay the result of the changed files check
     *
     * @return html  HTML
     */
    public function fileSystem()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {

            if (isset($_REQUEST['destinationBuild'])) {
                $tobuild = $_REQUEST['destinationBuild'];
                $access_token = $_REQUEST['access_token'];
                $frombuild = Yii::app()->getConfig("buildnumber");

                $updateModel = new UpdateForm();
                $changedFiles = $updateModel->getChangedFiles($tobuild);

                if ($changedFiles->result) {
                    $aData = $updateModel->getFileStatus($changedFiles->files);

                    $aData['html_from_server'] = (isset($changedFiles->html)) ? $changedFiles->html : '';
                    $aData['datasupdateinfo'] = $this->_parseToView($changedFiles->files);
                    $aData['destinationBuild'] = $tobuild;
                    $aData['updateinfo'] = $changedFiles->files;
                    $aData['access_token'] = $access_token;

                    return $this->controller->renderPartial('update/updater/steps/_fileSystem', $aData, false, false);
                }
                return $this->_renderError($changedFiles);
            }
            return $this->_renderErrorString("unknown_destination_build");
        }
    }

    /**
     * backup files
     * @return html
     */
    public function backup()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            if (Yii::app()->request->getPost('destinationBuild')) {
                $destinationBuild = Yii::app()->request->getPost('destinationBuild');
                $access_token     = $_REQUEST['access_token'];

                if (Yii::app()->request->getPost('datasupdateinfo')) {
                    $updateinfos = (array) json_decode(base64_decode(Yii::app()->request->getPost('datasupdateinfo')), true);

                    $updateModel = new UpdateForm();
                    $backupInfos = $updateModel->backupFiles($updateinfos);

                    if ($backupInfos->result) {
                        $dbBackupInfos = $updateModel->backupDb($destinationBuild);
                        // If dbBackup fails, it will just provide a warning message : backup manually

                        $aData['dbBackupInfos'] = $dbBackupInfos;
                        $aData['basefilename'] = $backupInfos->basefilename;
                        $aData['tempdir'] = $backupInfos->tempdir;
                        $aData['datasupdateinfo'] = $this->_parseToView($updateinfos);
                        $aData['destinationBuild'] = $destinationBuild;
                        $aData['access_token'] = $access_token;
                        return $this->controller->renderPartial('update/updater/steps/_backup', $aData, false, false);

                    } else {
                        $error = $backup->error;
                    }
                } else {
                    $error = "no_updates_infos";
                }
            } else {
                $error = "unknown_destination_build";
            }
            return $this->_renderErrorString($error);
        }
    }

    /**
     * Display step4
     * @return html
     */
    function step4()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            if (Yii::app()->request->getPost('destinationBuild')) {
                $destinationBuild = Yii::app()->request->getPost('destinationBuild');
                $access_token     = $_REQUEST['access_token'];

                if (Yii::app()->request->getPost('datasupdateinfo')) {
                    $updateinfos = json_decode(base64_decode(Yii::app()->request->getPost('datasupdateinfo')), true);

                    // this is the last step - Download the zip file, unpack it and replace files accordingly
                    $updateModel = new UpdateForm();
                    $file = $updateModel->downloadUpdateFile($access_token, $destinationBuild);

                    if ($file->result) {
                        $unzip = $updateModel->unzipUpdateFile();
                        if ($unzip->result) {
                            $remove = $updateModel->removeDeletedFiles($updateinfos);
                            if ($remove->result) {
                                // Should never bug (version.php is checked before))
                                $updateModel->updateVersion($destinationBuild);
                                $updateModel->destroyGlobalSettings();
                                $updateModel->removeTmpFile('update.zip');
                                $updateModel->removeTmpFile('comfort_updater_cookie.txt');

                                Yii::app()->session['update_result'] = null;
                                Yii::app()->session['security_update'] = null;
                                $today = new DateTime("now");
                                Yii::app()->session['next_update_check'] = $today->add(new DateInterval('PT6H'));

                                // TODO : aData should contains information about each step
                                return $this->controller->renderPartial('update/updater/steps/_final', array('destinationBuild'=>$destinationBuild), false, false);
                            } else {
                                $error = $remove->error;
                            }
                        } else {
                            $error = $unzip->error;
                        }
                    } else {
                        $error = $file->error;
                    }
                } else {
                    $error = "no_updates_infos";
                }
            } else {
                $error = "unknown_destination_build";
            }
            return $this->_renderErrorString($error);
        }
    }

    /**
     * This function update the updater
     * It is called from the view _updater_update.
     * The view _updater_update is called by the ComfortUpdate server during the getWelcome step if the updater version is not the minimal required one.
     * @return html the welcome message
     */
    public function updateUpdater()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            if (Yii::app()->request->getPost('destinationBuild')) {
                $destinationBuild = Yii::app()->request->getPost('destinationBuild');
                $updateModel = new UpdateForm();

                $localChecks = $updateModel->getLocalChecksForUpdater();

                if ($localChecks->result) {
                    $file = $updateModel->downloadUpdateUpdaterFile($destinationBuild);

                    if ($file->result) {
                        $unzip = $updateModel->unzipUpdateUpdaterFile();
                        if ($unzip->result) {
                            $updateModel->removeTmpFile('update_updater.zip');
                            $updateModel->removeTmpFile('comfort_updater_cookie.txt');
                            SettingGlobal::setSetting('updateavailable', '0');
                            SettingGlobal::setSetting('updatebuild', '');
                            SettingGlobal::setSetting('updaterversions', '');
                            Yii::app()->session['update_result'] = null;
                            Yii::app()->session['next_update_check'] = null;
                            return $this->controller->renderPartial('update/updater/steps/_updater_updated', array('destinationBuild'=>$destinationBuild), false, false);
                        } else {
                            $error = $unzip->error;
                        }
                    } else {
                        $error = $file->error;
                    }
                } else {
                    return $this->controller->renderPartial('update/updater/welcome/_error_files_update_updater', array('localChecks'=>$localChecks), false, false);
                }

            }
            return $this->_renderErrorString($error);
        }
    }

    /**
     * This return the subscribe message
     * @return html the welcome message
     */
    public function getnewkey()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            // We want to call the server to display the subscribe message
            // So if needed, we can display a specific html message (like we do for update to LTS with a free key)
            // To force server to render the subscribe message, we call for the last 2.06+ release (which need at least a free key)
            $updateModel = new UpdateForm();
            $welcome = $updateModel->getWelcomeMessage(null, '160129'); //$updateKey
            echo $this->_renderWelcome($welcome);
        }
    }

    /**
     * This function create or update the LS update key
     * @return html
     */
    public function submitkey()
    {

        if (Permission::model()->hasGlobalPermission('superadmin')) {
            if (Yii::app()->request->getPost('keyid')) {
                // We trim it, just in case user added a space...
                $submittedUpdateKey = trim(Yii::app()->request->getPost('keyid'));

                $updateModel = new UpdateForm();
                $check = $updateModel->checkUpdateKeyonServer($submittedUpdateKey);
                if ($check->result) {
                    // If the key is validated by server, we update the local database with this key
                    $updateKey = $updateModel->setUpdateKey($submittedUpdateKey);
                    $check = new stdClass();
                    $check->result = true;
                    $check->view = "key_updated";
                }
                // then, we render the what returned the server (views and key infos or error )
                echo $this->_renderWelcome($check);
            } else {
                return $this->_renderErrorString("key_null");
            }
        }
    }



    /**
     * Update database
     */
    public function db($continue = null)
    {
        Yii::app()->loadHelper("update/update");
        if (isset($continue) && $continue == "yes") {
            $aViewUrls['output'] = CheckForDBUpgrades($continue);
            $aData['display']['header'] = false;
        } else {
            $aData['display']['header'] = true;
            $aViewUrls['output'] = CheckForDBUpgrades();
        }

        $aData['updatedbaction'] = true;

        $this->_renderWrappedTemplate('update', $aViewUrls, $aData);
    }

    /**
     * For updates from the old updater.
     */
    public function step4b()
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            if (!isset(Yii::app()->session['installlstep4b'])) {
                die();
            }
            $aData = Yii::app()->session['installlstep4b'];
            unset (Yii::app()->session['installlstep4b']);
            $this->_renderWrappedTemplate('update/updater/steps', '_old_step4b', $aData);
        }
    }

    /**
     * This function change the notification state : big alert notification 1, or small one 0
     * It's called via ajax from view adminmenu
     */
    public function notificationstate($state = '0')
    {
        Yii::app()->session['notificationstate'] = $state;
        return '1';
    }
    /**
     * this function render the update buttons
     * @param string $crosscheck
     */
    private function _getButtons($crosscheck)
    {
        if (Permission::model()->hasGlobalPermission('superadmin')) {
            $updateModel = new UpdateForm();
            $serverAnswer = $updateModel->getUpdateInfo($crosscheck);

            // TODO : if no update available, set session about  it...

            if ($serverAnswer->result) {
                unset($serverAnswer->result);
                return $this->controller->renderPartial('//admin/update/check_updates/update_buttons/_updatesavailable', array('updateInfos' => $serverAnswer), false, false);
            }
            // Error : we build the error title and messages
            return $this->controller->renderPartial('//admin/update/check_updates/update_buttons/_updatesavailable_error', array('serverAnswer' => $serverAnswer), false, false);
        }
    }

    /**
     * This method render the welcome/subscribe/key_updated message
     * @param obj $serverAnswer the answer return by the server
     */
    private function _renderWelcome($serverAnswer)
    {
        if ($serverAnswer->result) {
            // Available views (in /admin/update/welcome/ )
            $views = array('welcome', 'subscribe', 'key_updated', 'updater_update');
            if (in_array($serverAnswer->view, $views)) {
                $sValidityDate = '';
                if (isset($serverAnswer->key_infos->validuntil)) {
                    $sValidityDate = convertToGlobalSettingFormat($serverAnswer->key_infos->validuntil);
                }
                return $this->controller->renderPartial('//admin/update/updater/welcome/_'.$serverAnswer->view, array('serverAnswer' => $serverAnswer, 'sValidityDate'=>$sValidityDate), false, false);
            } else {
                $serverAnswer->result = false;
                $serverAnswer->error = "unknown_view";
            }
        }
        echo $this->_renderError($serverAnswer);

    }


    /**
     * This method renders the error view
     * @param object $errorObject
     * @return html
     */
    private function _renderError($errorObject)
    {
        echo $this->controller->renderPartial('//admin/update/updater/_error', array('errorObject' => $errorObject), false, false);
    }

    /**
     * This method convert a string to an error object, and then render the error view
     * @param string $error the error message
     * @return html
     */
    private function _renderErrorString($error)
    {
            $errorObject = new stdClass();
            $errorObject->result = false;
            $errorObject->error = $error;
            return $this->_renderError($errorObject);
    }

    /**
     * This function convert the huge updateinfos array to a base64 string, so it can be parsed to the view to be inserted in an hidden input element.
     *
     * @param array $updateinfos the udpadte infos array returned by the update server
     * @return $string
     */
    private function _parseToView($updateinfos)
    {
        $data = json_encode($updateinfos);
        return base64_encode($data);
    }

}
