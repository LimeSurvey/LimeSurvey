import React, { useMemo, useEffect, useState } from 'react'
import { Dropdown } from 'react-bootstrap'
import classNames from 'classnames'

import { ACCESS_MODES } from 'helpers'
import { useSurveyArchive, useSurveyUpdatePermission } from 'hooks'
import { ArrowDownIcon, CheckIcon } from 'components/icons'

import {
  handleSurveyAccessModeChange,
  getSurveyAccessModeOptions,
} from './surveyAccessModeHandler'

export const SurveyAccessModeSelector = ({
  parentName = null,
  survey,
  currentSurveyAccessMode,
  onSurveyAccessModeChange,
  createBufferOperation,
  addToBuffer,
  update,
}) => {
  const { fetchFilteredSurveyArchivesByBase } = useSurveyArchive(survey.sid)
  const surveyAccessModeChangeOptions = useMemo(
    () => getSurveyAccessModeOptions(),
    []
  )
  const [selectedAccessMode, setSelectedAccessMode] = useState(
    survey?.access_mode === ACCESS_MODES.CLOSED
      ? surveyAccessModeChangeOptions.closed.key
      : surveyAccessModeChangeOptions.open.key
  )

  const hasUpdatePermission = useSurveyUpdatePermission(survey)

  const onAccessModeSelect = (accessModeKey) => {
    const newAccessMode = surveyAccessModeChangeOptions[accessModeKey]
    handleSurveyAccessModeChange({
      survey,
      currentSurveyAccessMode,
      newAccessMode,
      setSelectedAccessMode,
      onSurveyAccessModeChange,
      createBufferOperation,
      addToBuffer,
      update,
    })

    setSelectedAccessMode(newAccessMode.key)
  }

  useEffect(() => {
    fetchFilteredSurveyArchivesByBase(survey.sid)
  }, [survey, selectedAccessMode])

  return (
    <div className="survey-access-mode mb-3" data-testid="access-mode-heading">
      <h5 className="med16-c mb-4">
        {t('Select how you want to share your survey')}
      </h5>
      <div
        className={`row ${parentName !== 'Overview' ? 'gx-3' : 'gx-1'} ${!hasUpdatePermission ? 'disable-settings' : ''}`}
      >
        <div
          className={classNames(
            'fill-grape col-auto d-flex align-items-center',
            { 'pe-0': parentName !== 'Overview' }
          )}
        >
          <div className="d-flex align-items-center me-2">
            {surveyAccessModeChangeOptions[selectedAccessMode].icon}
          </div>
        </div>
        <div
          className={classNames('col d-flex flex-grow-1 flex-column', {
            'ps-0': parentName !== 'Overview',
          })}
        >
          <div className="flex-grow-1">
            <Dropdown
              className="survey-access-mode-dropDown"
              onSelect={(eventKey) => {
                if (hasUpdatePermission) {
                  onAccessModeSelect(eventKey)
                }
              }}
            >
              <Dropdown.Toggle
                className={classNames(
                  'access-mode-toggle text-start border-0 ps-2 pe-2',
                  { disable: !hasUpdatePermission }
                )}
                data-testid="access-mode-toggle"
              >
                <div className="d-flex align-items-start justify-content-start">
                  <div className="med14-c text-black">
                    {surveyAccessModeChangeOptions[selectedAccessMode].label}
                  </div>
                  <ArrowDownIcon
                    className="access-mode-dropdown-icon ms-3"
                    height={20}
                    width={20}
                  />
                </div>
              </Dropdown.Toggle>

              <Dropdown.Menu
                className="w-100"
                data-testid="access-mode-menu"
                role="menu"
              >
                {Object.entries(surveyAccessModeChangeOptions).map(
                  ([key, option]) => (
                    <Dropdown.Item
                      eventKey={key}
                      key={key}
                      className={classNames(
                        'survey-access-mode-dropdown-item',
                        { 'disable ': !hasUpdatePermission }
                      )}
                      role="menuitem"
                    >
                      <div
                        className={classNames('row gx-1 p-2', {
                          'active-access-mode': selectedAccessMode === key,
                        })}
                      >
                        <div className="col-auto d-flex align-items-center">
                          <span>{option.icon}</span>
                        </div>
                        <div className="col">
                          <div className="med14-c">{option.label}</div>
                          <div className="reg14 text-wrap">
                            {option.description}
                          </div>
                        </div>
                        <div className="col-auto ps-2 d-flex align-items-center justify-content-end">
                          {selectedAccessMode === key && (
                            <div data-testid="check-icon">
                              <CheckIcon />
                            </div>
                          )}
                        </div>
                      </div>
                    </Dropdown.Item>
                  )
                )}
              </Dropdown.Menu>
            </Dropdown>
          </div>
          <div className="reg14 ps-2 pb-2">
            {surveyAccessModeChangeOptions[selectedAccessMode].description}
          </div>
        </div>
      </div>
    </div>
  )
}
