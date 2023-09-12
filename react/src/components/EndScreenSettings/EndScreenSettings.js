import { Button, Form } from 'react-bootstrap'
import React from 'react'
import classNames from 'classnames'
import { XLg } from 'react-bootstrap-icons'

import { useFocused, useSurvey } from 'hooks'
import { SideBarHeader } from 'components/SideBar'
import { SettingsWrapper, Input } from 'components/UIComponents'
import { ExternalLinkIcon } from 'components/icons'

// todo: check the correct keys for the attributes
export const EndScreenSettings = ({ surveyId }) => {
  const {
    survey: { languageSettings, language },
    update,
  } = useSurvey(surveyId)
  const { focused = {}, unFocus, setFocused } = useFocused()

  const handleUpdate = (updated) => {
    const updateData = {
      ...languageSettings,
    }

    updateData[language] = {
      ...updateData[language],
      ...updated,
    }

    update({ languageSettings: updateData })
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
          End screen settings
        </div>
        <Button variant="link" style={{ padding: 0 }} onClick={unFocus}>
          <XLg stroke={'black'} fontWeight={800} color="black" size={15} />
        </Button>
      </SideBarHeader>
      <SettingsWrapper title="Basic">
        <Input
          dataTestId="end-screen-button-title"
          onChange={({ target: { value } }) =>
            handleUpdate({ endScreenButtonTitle: value })
          }
          value={languageSettings[language]?.endScreenButtonTitle}
          labelText={'URL description'}
          className="mt-3"
        />
        <Input
          dataTestId="end-screen-forwarding-button-url"
          onChange={({ target: { value } }) =>
            handleUpdate({ endScreenForwardingButtonUrl: value })
          }
          value={languageSettings[language].endScreenForwardingButtonUrl}
          labelText={'End URL'}
          className="mt-3"
        />
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
