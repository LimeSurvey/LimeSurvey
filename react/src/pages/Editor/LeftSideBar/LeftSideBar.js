import React, { useEffect, useState } from 'react'
import classNames from 'classnames'
import { StructureIcon, SurveySettingsIcon } from 'components/icons'
import { SurveySettings } from 'components/SurveySettings/SurveySettings'

import { SideBar } from 'components/SideBar'
import { SurveyStructure } from 'components/SurveyStructure'
import { useSurvey, useAppState } from 'hooks'

export const LeftSideBar = ({ surveyId }) => {
  const { survey = {}, update } = useSurvey(surveyId)
  const [isStructure, setIsStructure] = useState(true)

  const [editorStructurePanelOpen, setEditorStructurePanelOpen] = useAppState(
    'editorStructurePanelOpen',
    true
  )

  const [settingsPanelOpen, setSettingsPanelOpen] = useAppState(
    'settingsPanelOpen',
    false
  )

  useEffect(() => {
    setEditorStructurePanelOpen(true)
    setSettingsPanelOpen(false)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  return (
    <SideBar visible={true} className={classNames('sidebar', 'sidebar-left')}>
      <div className="d-flex" style={{ height: '100%' }}>
        <div className="" style={{ width: '52px' }}>
          <div
            onClick={() => {
              setEditorStructurePanelOpen(true)
              setIsStructure(true)
              setSettingsPanelOpen(false)
            }}
            className="cursor-pointer d-flex justify-content-center"
          >
            <StructureIcon
              className={`${
                editorStructurePanelOpen && isStructure
                  ? 'text-white'
                  : 'text-black'
              } fill-current`}
              bgColor={`${
                editorStructurePanelOpen && isStructure ? '#333641' : '#EEEFF7'
              }`}
            />
          </div>
          <div
            onClick={() => {
              setEditorStructurePanelOpen(true)
              setSettingsPanelOpen(true)
              setIsStructure(false)
            }}
            className="mt-3 cursor-pointer d-flex justify-content-center"
          >
            <SurveySettingsIcon
              className={`${
                editorStructurePanelOpen && settingsPanelOpen
                  ? 'text-white'
                  : 'text-black'
              } fill-current`}
              bgColor={`${
                editorStructurePanelOpen && settingsPanelOpen
                  ? '#333641'
                  : '#EEEFF7'
              }`}
            />
          </div>
        </div>
        {editorStructurePanelOpen && (
          <div>
            {isStructure && (
              <SurveyStructure
                survey={survey}
                surveyId={surveyId}
                update={(questionGroups) => update({ questionGroups })}
              />
            )}
            {settingsPanelOpen && <SurveySettings surveyId={surveyId} />}
          </div>
        )}
      </div>
    </SideBar>
  )
}
