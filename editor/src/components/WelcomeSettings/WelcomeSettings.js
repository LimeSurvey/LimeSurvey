import { Button } from 'react-bootstrap'
import React from 'react'
import classNames from 'classnames'

import { isTrue, createBufferOperation } from 'helpers'
import { useBuffer, useFocused, useSurvey } from 'hooks'
import { SideBarHeader } from 'components/SideBar'
import { SettingsWrapper, ToggleButtons } from 'components/UIComponents'
import { ImageAttributes } from 'components/QuestionSettings/attributes'

import { CloseIcon } from '../icons'

export const WelcomeSettings = ({ surveyId }) => {
  const {
    survey: { showXQuestions, showWelcome, welcomeImage },
    update,
  } = useSurvey(surveyId)
  const { focused = {}, unFocus, setFocused } = useFocused()
  const { addToBuffer } = useBuffer()

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
        <ImageAttributes
          update={(welcomeImage) => handleUpdate({ welcomeImage })}
          value={welcomeImage}
          isSimpleSettings={true}
          wrapperClass={'ms-3 mt-3'}
        />
      </SettingsWrapper>
    </div>
  )
}
