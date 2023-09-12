import React, { useEffect } from 'react'

import { RightSideBar } from './RightSideBar'
import { useFocused, useSurvey } from 'hooks'

export default {
  title: 'Page/Editor/RightSideBar',
  component: RightSideBar,
}

const surveyId = '78f91e52-6028-11ed-82e1-7ac846e3af9d'

export const Basic = () => {
  const { survey } = useSurvey(surveyId)
  const { setFocused } = useFocused()

  useEffect(() => {
    if (survey?.questionGroups) {
      setFocused(survey.questionGroups[0].questions[0], 0, 0)
    }
  }, [survey?.questionGroups, setFocused])

  return <RightSideBar surveyId={surveyId} />
}
