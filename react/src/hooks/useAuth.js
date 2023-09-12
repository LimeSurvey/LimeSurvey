import { useQuery, useMutation } from '@tanstack/react-query'

import { queryClient } from 'query'

export const useAuth = () => {
  const { data: auth } = useQuery({
    queryKey: ['auth'],
    queryFn: () => {
      return { token: null }
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

  const isLoggedIn = !!auth && !!auth.token
  const isPending =
    loginMutation && (loginMutation.isLoading || loginMutation.isError)

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

  return { isLoggedIn, isPending, login, loginMutation, setAuth }
}

export default useAuth
