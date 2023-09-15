import { useQuery, useMutation } from '@tanstack/react-query'
import { useCookies } from 'react-cookie'

import { queryClient } from 'query'

export const useAuth = () => {
  const [ cookies ] = useCookies(['LS_AUTH_INIT'])

  const { data: auth } = useQuery({
    queryKey: ['auth'],
    queryFn: () => {
      return { token: null, expires: null }
    },
    staleTime: Infinity,
    cacheTime: Infinity,
  })

  const setAuth = (value) => {
    return queryClient.setQueryData(['auth'], value)
  }

  const loginMutation = useMutation({
    mutationFn: async (loginData) => {
      if (auth && auth.token) {
        return auth
      }
      const params = new URLSearchParams()
      const { username, password } = loginData
      params.append('username', username)
      params.append('password', password)
      const response = await fetch('/session')
      return response.data
    },
    onSuccess: (data) => {
      // set bearer token to header for all future requests
    },
  })

  const isLoggedIn = process.env.REACT_APP_DEMO_MODE
    || (!!auth && !!auth.token)
  const isPending =
    loginMutation && (loginMutation.isLoading || loginMutation.isError)

  // If we are not logged-in and not in the process of logging-in
  // - and there is an auth-init cookie init auth from the cookie
  if (!isLoggedIn && !isPending && cookies.LS_AUTH_INIT) {
    setAuth({
      token: cookies.LS_AUTH_INIT.token,
      expires: cookies.LS_AUTH_INIT.expires
    })
  }

  const login = () => {
    if (!isLoggedIn && !isPending) {
      console.log({
        isLoggedIn,
      })
      loginMutation.mutate({
        username: 'admin',
        password: 'password',
      })
    }
  }

  return {
    isLoggedIn,
    isPending,
    login,
    loginMutation,
    setAuth,
    token: auth ? auth.token : null
  }
}

export default useAuth
