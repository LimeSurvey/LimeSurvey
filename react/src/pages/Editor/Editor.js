import React, { useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import Container from 'react-bootstrap/Container'

import { AuthGate } from 'components/AuthGate'
import { Survey } from 'components/Survey'
import { useAppState, useElementClick, useFocused } from 'hooks'

import { TopBar } from './TopBar/TopBar'
import { LeftSideBar } from './LeftSideBar/LeftSideBar'
import { RightSideBar } from './RightSideBar/RightSideBar'

export const Editor = () => {
  const params = useParams()
  const [surveyId, setSurveyId] = useState(params.id ? params.id : 282267)
  const [editorSettingsPanelOpen] = useAppState('editorSettingsPanelOpen', true)

  const { unFocus } = useFocused()

  const handleClickInside = () => {
    unFocus()
  }

  const ref = useElementClick(handleClickInside, false)

  useEffect(() => {
    if (params.id) {
      setSurveyId(params.id)
    }
  }, [params.id])

  return (
    <React.Fragment>
      <AuthGate>
        <TopBar surveyId={surveyId} />
        <Container className="p-0" fluid>
          <div id="content" className="d-flex">
            <LeftSideBar surveyId={surveyId} />
            <div className="main-body">
              <div className="survey-part">
                <Survey id={surveyId} />
              </div>
              {!editorSettingsPanelOpen && (
                <div className="inner-wrap" ref={ref} />
              )}
            </div>
            <RightSideBar surveyId={surveyId} />
          </div>
        </Container>
      </AuthGate>
      <AuthGate authorised={false}>Please Login</AuthGate>
    </React.Fragment>
  )
}
