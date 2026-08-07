import React from 'react'
import { Button } from 'react-bootstrap'
import classNames from 'classnames'

import { useBuffer, useFocused, useSurvey } from 'hooks'
import { SideBarHeader } from 'components/SideBar'
import { SettingsWrapper, Input } from 'components/UIComponents'

import { createBufferOperation } from '../../helpers'
import { CloseIcon } from '../icons'

// todo: check the correct keys for the attributes
export const EndScreenSettings = ({ surveyId }) => {
  const {
    survey: { languageSettings, language },
    update,
  } = useSurvey(surveyId)
  const { focused = {}, unFocus, setFocused } = useFocused()
  const { addToBuffer } = useBuffer()

  const handleUpdate = (updated) => {
    const updateData = {
      ...languageSettings,
    }

    updateData[language] = {
      ...updateData[language],
      ...updated,
    }

    const operation = createBufferOperation(null)
      .languageSetting()
      .update({
        [language]: updated,
      })
    addToBuffer(operation)
    update({ languageSettings: updateData })
  }

  const handleOnQuestionCodeClick = () => {
    setFocused(focused)
  }

  const settings = language ? languageSettings[language] : {}
  return (
    <div
      className={classNames('survey-settings')}
      data-testid="end-screen-settings"
    >
      <SideBarHeader className="right-side-bar-header primary">
        <div
          onClick={handleOnQuestionCodeClick}
          className="focused-question-code"
        >
          {t('End screen settings')}
        </div>
        <Button variant="link" style={{ padding: 0 }} onClick={unFocus}>
          <CloseIcon className="text-black fill-current" />
        </Button>
      </SideBarHeader>
      <SettingsWrapper simpleSettings={true} isDefaultOpen={true}>
        <Input
          dataTestId="end-screen-button-title"
          onChange={({ target: { value } }) =>
            handleUpdate({ urlDescription: value })
          }
          value={settings.urlDescription}
          labelText={'URL description'}
          className="ms-3 mt-3"
          noPermissionDisabled={true}
        />
        <Input
          dataTestId="end-screen-forwarding-button-url"
          onChange={({ target: { value } }) => handleUpdate({ url: value })}
          value={settings.url}
          labelText={'End url'}
          className="ms-3 mt-3"
          noPermissionDisabled={true}
        />
      </SettingsWrapper>
    </div>
  )
}
