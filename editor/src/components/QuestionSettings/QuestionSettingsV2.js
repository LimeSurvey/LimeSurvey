import { useMemo } from 'react'

import { useQuestionAttributes } from 'hooks'
import { QuestionSettings } from './QuestionSettings'
import { buildSettingsFromAttributes } from './dynamic'

export const QuestionSettingsv2 = ({ surveyId, focused = {} }) => {
  const {
    attributes: { attributesDetails = {} },
  } = useQuestionAttributes()

  const themeAttributes =
    attributesDetails[focused.questionThemeName]?.attributes

  // An empty result makes QuestionSettings fall back to the hardcoded settings.
  const settings = useMemo(
    () => buildSettingsFromAttributes(themeAttributes),
    [themeAttributes]
  )

  return <QuestionSettings surveyId={surveyId} settings={settings} />
}
