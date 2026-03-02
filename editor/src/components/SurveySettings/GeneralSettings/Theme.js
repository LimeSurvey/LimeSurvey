import React from 'react'

import { SurveySetting } from '../SurveySetting'
import { useSurvey } from '../../../hooks'

export const Theme = (props) => {
  const { survey } = useSurvey(props.surveyId)
  let templatePreview = survey?.templatePreview
  if (process.env.REACT_APP_DEV_MODE && templatePreview) {
    templatePreview = process.env.REACT_APP_SITE_URL + templatePreview
  }

  return (
    <>
      <SurveySetting {...props} />
      {templatePreview && (
        <img
          className="img-thumbnail theme-preview p-0 mt-4 rounded-0"
          src={templatePreview}
          alt={t('Theme preview')}
        />
      )}
    </>
  )
}
