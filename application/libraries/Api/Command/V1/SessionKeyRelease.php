<?php

namespace LimeSurvey\Api\Command\V1;

use CDbCriteria;
use Session;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Mixin\CommandResponse;

class SessionKeyRelease implements CommandInterface
{
    use CommandResponse;

    /**
     * Run session key release command.
     *
     * @access public
     * @param \LimeSurvey\Api\Command\Request\Request $request
     * @return \LimeSurvey\Api\Command\Response\Response
     */
    public function run(Request $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        Session::model()
            ->deleteAllByAttributes(array(
                'id' => $sessionKey
            ));
        $criteria = new CDbCriteria();
        $criteria->condition = 'expire < ' . time();
        Session::model()->deleteAll($criteria);
        return $this->responseSuccess('OK');
    }
}
