import React from 'react'

import { useAuth } from 'hooks'

export const AuthGate = ({ authorised: authorised=true, children }) => {
  const auth = useAuth()

  return auth.isLoggedIn || !authorised ? (
    <React.Fragment>
      {children}
    </React.Fragment>
  ) : null;
}

export default AuthGate
