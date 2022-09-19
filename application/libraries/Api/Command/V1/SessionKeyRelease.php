<?php

namespace LimeSurvey\Api\Command\V1;

use CDbCriteria;
use Session;
use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\Request\Request;
use LimeSurvey\Api\Command\Response\Response;
use LimeSurvey\Api\Command\Response\Status\StatusSuccess;

class SessionKeyRelease implements CommandInterface
{
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
        return new Response('OK', new StatusSuccess);
    }
}
