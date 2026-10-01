import { useParams } from 'react-router-dom'

import { UserService } from 'services'
import { getApiUrl } from 'helpers'

import useAuth from './useAuth'

export const useUserService = () => {
  const auth = useAuth()
  const { userId } = useParams()

  return new UserService(auth, userId, getApiUrl())
}
