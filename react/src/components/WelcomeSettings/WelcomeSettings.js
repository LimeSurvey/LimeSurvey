import { Button, Form } from 'react-bootstrap'
import React from 'react'
import classNames from 'classnames'
import { XLg } from 'react-bootstrap-icons'

import { useFocused, useSurvey } from 'hooks'
import { SideBarHeader } from 'components/SideBar'
import { SettingsWrapper, ToggleButtons } from 'components/UIComponents'
import { ExternalLinkIcon } from 'components/icons'
import { ImageSettings } from 'components/QuestionSettings/LayoutSettings/ImageSettings/ImageSettings'

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

  const handleUpdate = (prop) => {
    update(prop)
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
          Welcome settings
        </div>
        <Button variant="link" style={{ padding: 0 }} onClick={unFocus}>
          <XLg stroke={'black'} fontWeight={800} color="black" size={15} />
        </Button>
      </SideBarHeader>
      <SettingsWrapper title="Layout">
        <div className="mb-2">
          <ToggleButtons
            labelText="Toggle screen"
            value={showWelcome}
            onChange={(showWelcome) =>
              handleUpdate({ showWelcome: showWelcome })
            }
          />
        </div>
        <ImageSettings
          imageAlign={imageAlign}
          image={welcomeImage}
          imageBrightness={imageBrightness || 0}
          handleUpdate={(info) => {
            const updated = {}
            if (info.image !== undefined) {
              updated.welcomeImage = info.image
            }

            if (info.imageAlign !== undefined) {
              updated.imageAlign = info.imageAlign
            }

            if (info.imageBrightness !== undefined) {
              updated.imageBrightness = info.imageBrightness
            }

            handleUpdate(updated)
          }}
        />
        <div className="mt-3">
          <ToggleButtons
            id="question-counter"
            labelText="Show “There are X questions in this survey.”"
            value={showXQuestions}
            onChange={(showXQuestions) => handleUpdate({ showXQuestions })}
            onOffToggle
          />
        </div>
      </SettingsWrapper>
      <SettingsWrapper title="Privacy policy">
        <div>
          <Form.Label>
            To enter privacy policy texts go to survey settings.
          </Form.Label>
          <Button variant="secondary" className="d-flex align-items-center">
            <span className="me-1">Edit privacy policy</span>
            <ExternalLinkIcon className="text-white fill-current" />
          </Button>
        </div>
      </SettingsWrapper>
    </div>
  )
}
