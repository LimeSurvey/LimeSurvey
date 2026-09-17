<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Bug #19095: changing a user's password (self-service or via the
 * forgot-password reset link) did not invalidate any other already
 * authenticated session for that user.
 *
 * Adds a per-user session token that is regenerated whenever the password
 * changes (see User::setPassword()) and cached in the session at login
 * (see LSUserIdentity::postLogin()). Any other session still carrying the
 * previous token is logged out on its next request, since it no longer
 * matches the value stored on the user record (see
 * LSApplicationTrait::getCurrentUserId()).
 */
class Update_715 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->addColumn('{{users}}', 'session_token', 'string(64) NULL');
    }
}
