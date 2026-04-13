import { useParams } from 'react-router-dom'

import { SurveyService } from 'services'
import { getApiUrl } from 'helpers'

import useAuth from './useAuth'

export const useSurveyService = () => {
  const auth = useAuth()
  const { surveyId } = useParams()

  return {
    surveyService: new SurveyService(auth, surveyId, getApiUrl()),
  }
}
