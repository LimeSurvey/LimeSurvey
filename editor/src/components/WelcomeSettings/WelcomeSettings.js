import { Button } from 'react-bootstrap'
import React from 'react'
import classNames from 'classnames'
import { useNavigate } from 'react-router-dom'

import { isTrue, createBufferOperation, SURVEY_MENU_TITLES } from 'helpers'
import { getSurveyPanels } from 'helpers/options'
import { useBuffer, useFocused, useSurvey } from 'hooks'
import { SideBarHeader } from 'components/SideBar'
import { SettingsWrapper, ToggleButtons } from 'components/UIComponents'
import { GetImageAttributes } from 'components/QuestionSettings/attributes/getImageAttributes'

import { CloseIcon, SettingsIcon } from '../icons'

export const WelcomeSettings = ({ surveyId }) => {
  const {
    survey: {
      imageAlign,
      showXQuestions,
      showWelcome,
      welcomeImage,
      imageBrightness,
    },
    update,
  } = useSurvey(surveyId)
  const { focused = {}, unFocus, setFocused } = useFocused()
  const { addToBuffer } = useBuffer()
  const navigate = useNavigate()

  const handleUpdate = (prop) => {
    update(prop)

    const operation = createBufferOperation(surveyId)
      .survey()
      .update({ ...prop })

    addToBuffer(operation)
  }

  const handleOnQuestionCodeClick = () => {
    setFocused(focused)
  }

  const handlePrivacyDetailsClick = () => {
    navigate(
      `/survey/${surveyId}/${getSurveyPanels().settings.panel}/${SURVEY_MENU_TITLES.dataSecurity}`
    )
  }

  return (
    <div className={classNames('survey-settings')}>
      <SideBarHeader className="right-side-bar-header primary">
        <div
          onClick={handleOnQuestionCodeClick}
          className="focused-question-code"
        >
          {t('Welcome screen settings')}
        </div>
        <Button variant="link" style={{ padding: 0 }} onClick={unFocus}>
          <CloseIcon className="text-black fill-current" />
        </Button>
      </SideBarHeader>
      <SettingsWrapper simpleSettings={true} isDefaultOpen={true}>
        <div className="ms-3 mb-3">
          <ToggleButtons
            labelText={t('Show welcome screen')}
            value={isTrue(showWelcome)}
            onChange={(showWelcome) =>
              handleUpdate({ showWelcome: showWelcome })
            }
            noPermissionDisabled={true}
          />
        </div>
        <GetImageAttributes
          imageAlign={imageAlign}
          value={welcomeImage}
          imageBrightness={imageBrightness || 0}
          update={(info) => handleUpdate(info)}
        />
        <div className="ms-3 mt-3">
          <ToggleButtons
            id="question-counter"
            labelText={t('Show "There are X questions in this survey."')}
            value={isTrue(showXQuestions)}
            onChange={(showXQuestions) => handleUpdate({ showXQuestions })}
            onOffToggle
            noPermissionDisabled={true}
          />
        </div>
        <div className="ms-3 mt-3 pt-3 privacy-settings-link-border-top">
          <Button
            variant="link"
            className="privacy-settings-link"
            style={{ padding: 0, border: 'none' }}
            onClick={handlePrivacyDetailsClick}
            data-testid="privacy-details-link"
          >
            <SettingsIcon />
            {t('Define and edit privacy details')}
          </Button>
        </div>
      </SettingsWrapper>
    </div>
  )
}
