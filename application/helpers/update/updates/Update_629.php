<?php

namespace LimeSurvey\Helpers\Update;

class Update_629 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up(): void
    {
        // Update authType in twoFactorUsers table if it exists
        // Any type other than 'yubikey' will be set to 'totp'
        if (tableExists('{{twoFactorUsers}}')) {
            $this->db->createCommand()
                ->update(
                    '{{twoFactorUsers}}',
                    ['authType' => 'totp'],
                    "authType != 'yubi'"
                );
        }
    }
}
