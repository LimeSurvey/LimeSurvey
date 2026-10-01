import { useParams } from 'react-router-dom'

import { RestClient } from 'services'
import { getApiUrl } from 'helpers'

import useAuth from './useAuth'

export const useRestClient = () => {
  const auth = useAuth()
  const { surveyId } = useParams()

  return {
    restClient: new RestClient(`${getApiUrl}/${surveyId}`, auth.restHeaders),
  }
}
