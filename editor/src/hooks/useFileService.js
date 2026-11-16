import { useParams } from 'react-router-dom'

import { FileService } from 'services'
import { getApiUrl } from 'helpers'

import useAuth from './useAuth'

export const useFileService = () => {
  const auth = useAuth()
  const { surveyId } = useParams()

  return {
    fileService: new FileService(auth, getApiUrl(), surveyId),
  }
}
