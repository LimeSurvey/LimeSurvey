import { useEffect } from 'react'
import { useQuery } from '@tanstack/react-query'
import { useCookies } from 'react-cookie'

import { queryClient } from 'queryClient'
import { dayJsHelper } from 'helpers'

import { useAuthService } from './'

export const useAuth = () => {
  const { authService } = useAuthService()
  const [cookies, removeCookie] = useCookies(['LS_AUTH_INIT'])

  const { data: auth } = useQuery({
    queryKey: ['auth'],
    queryFn: async () => {
      const initData = queryClient.getQueryData(['auth']) || {}
      const restHeaders = getRestHeaders(initData)
      let result = initData
      if (isLoggedIn(initData) && getTokenAgeSeconds(initData) >= 60 * 30) {
        result = await authService.refresh(restHeaders)
      }
      return result
    },
    refetchOnMount: 'always',
    staleTime: Infinity,
    refetchInterval: 1000 * 60,
    meta: {
      persist: true,
    },
  })

  const setAuth = (value) => {
    return queryClient.setQueryData(['auth'], value)
  }

  const getRestHeaders = (auth) => {
    return {
      mode: 'cors',
      Authorization: `Bearer ${auth?.token}`,
      // ClientApplication is a custom header to
      // indicate that we are running the
      // LimeSurvey Single Page Application
      ClientApplication:
        process.env.REACT_APP_RELEASE + '@' + process.env.REACT_APP_COMMIT_HASH,
    }
  }

  const isLoggedIn = (auth) => {
    return (
      process.env.REACT_APP_DEMO_MODE === 'true' ||
      (!!auth && !!auth.expires && new Date(auth.expires) >= new Date())
    )
  }

  const getTokenAgeSeconds = (auth) => {
    return dayJsHelper().diff(dayJsHelper(auth.created), 'second')
  }

  // If we are not logged-in and not in the process of logging-in
  // - and there is an auth-init cookie init auth from the cookie
  useEffect(() => {
    if (isLoggedIn(auth) || !cookies.LS_AUTH_INIT?.token) {
      return
    }

    setAuth({
      userId: cookies.LS_AUTH_INIT?.userId,
      token: cookies.LS_AUTH_INIT?.token,
      created: cookies.LS_AUTH_INIT?.created,
      expires: cookies.LS_AUTH_INIT?.expires,
    })
  }, [cookies.LS_AUTH_INIT])

  const logout = () => {
    removeCookie('LS_AUTH_INIT')
    setAuth({})
  }

  return {
    isLoggedIn: isLoggedIn(auth),
    logout,
    restHeaders: getRestHeaders(auth),
    userId: auth?.userId,
    token: auth?.token,
  }
}

export default useAuth
