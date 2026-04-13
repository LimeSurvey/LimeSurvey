import { AuthService } from 'services'
import { getApiUrl } from 'helpers'

export const useAuthService = () => {
  return {
    authService: new AuthService(getApiUrl()),
  }
}
