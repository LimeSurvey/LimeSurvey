import { useParams } from 'react-router-dom'

import { SurveyGroupsService } from 'services'
import { getApiUrl } from 'helpers'

import useAuth from './useAuth'

export const useSurveyGroupsService = () => {
  const auth = useAuth()
  const { surveyId } = useParams()

  return {
    surveyGroupsService: new SurveyGroupsService(auth, surveyId, getApiUrl()),
  }
}
