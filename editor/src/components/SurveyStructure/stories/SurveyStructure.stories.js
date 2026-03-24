import React, { useEffect } from 'react'

import { useAppState, useSurvey } from 'hooks'

import { SurveyStructure } from '../../SurveyStructure'

export default {
  title: 'General/SurveyStructure',
  component: SurveyStructure,
}

export const Basic = () => {
  const { survey } = useSurvey()

  const [, editorStructurePanelOpen] = useAppState(
    'editorStructurePanelOpen',
    true
  )

  useEffect(() => {
    editorStructurePanelOpen(true)
  }, [])

  return <SurveyStructure survey={survey} />
}
