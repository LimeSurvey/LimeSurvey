import { getApiUrl } from 'helpers'
import useAuth from './useAuth'
import { TranslationsService } from '../services/translations.service'

export const useTranslationsService = () => {
  const auth = useAuth()

  return {
    translationsService: new TranslationsService(auth, getApiUrl()),
  }
}
