<?php

namespace LimeSurvey\Api\Command\V1;

use LimeSurvey\Api\Command\CommandInterface;
use LimeSurvey\Api\Command\CommandRequest;
use LimeSurvey\Api\Command\CommandResponse;

class SessionKeyRelease implements CommandInterface
{
    /**
     * Run session key release command.
     *
     * @access public
     * @param LimeSurvey\Api\Command\CommandRequest $request
     * @return LimeSurvey\Api\Command\CommandResponse
     */
    public function run(CommandRequest $request)
    {
        $sessionKey = (string) $request->getData('sessionKey');
        \Session::model()
            ->deleteAllByAttributes(array(
                'id' => $sessionKey
            ));
        $criteria = new \CDbCriteria();
        $criteria->condition = 'expire < ' . time();
        \Session::model()->deleteAll($criteria);
        return new CommandResponse('OK');
    }
}
