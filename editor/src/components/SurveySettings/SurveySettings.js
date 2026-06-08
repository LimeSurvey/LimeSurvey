import { useEffect, useMemo, useState, useCallback } from 'react'
import { useParams } from 'react-router-dom'

import { useAppState, useSurvey, useBuffer, useGlobalStates } from 'hooks'
import {
  STATES,
  getSettingValueFromSurvey,
  ignoreUpdate,
  createBufferOperation,
  Entities,
} from 'helpers'
import { getSurveyPanels } from 'helpers/options'
import { AdvancedOptionsSettings } from './AdvancedOptions'
import { SettingsBlocksRenderer } from './components/SettingsBlocksRenderer'
import { getSettingsBlocksInfo } from './utils/settingsConfig'
import {
  validateSettingUpdate,
  formatUpdateValue,
  getUpdateKey,
  createSurveyUpdate,
  createBufferOperationForSetting,
} from './utils/updateSettingHelpers'

export const SurveySettings = ({ id }) => {
  const { panel, menu } = useParams()
  const { survey, update } = useSurvey(id)

  const { operationsBuffer, addToBuffer } = useBuffer()
  const [rerenderSettings, setRerenderSettings] = useState({})

  const [surveyHash = {}] = useAppState(STATES.SURVEY_HASH)
  const [helperSettings = {}, setHelperSettings] = useAppState(
    STATES.HELPER_SETTINGS
  )
  const [, setSurveyRefreshRequired] = useAppState(
    STATES.SURVEY_REFRESH_REQUIRED
  )

  const [lastUpdatedSettings, setLastUpdatedSettings] = useState({
    value: '',
    previousValue: '',
    entity: '',
    setting: {},
  })

  const panelInfo = useMemo(() => {
    const panels = getSurveyPanels()
    return Object.values(panels).find((item) => item.panel === panel) || {}
  }, [panel])

  const globalStates = useGlobalStates(operationsBuffer, surveyHash)
  const settingsBlocksInfo = getSettingsBlocksInfo(survey)

  // Helper settings initialization
  useEffect(() => {
    const helperSettingsValue = {}
    // Loop through the blocks in surveySettings for the selected menu
    Object.entries(settingsBlocksInfo?.[menu] || {}).forEach(
      ([, blockSettings]) => {
        // Loop through each setting within the block
        Object.values(blockSettings.settings).forEach((setting) => {
          if (!setting.helperSetting) {
            return
          }

          const settingValue = getSettingValueFromSurvey(survey, setting)
          const value =
            typeof setting.formatDisplayValue === 'function'
              ? setting.formatDisplayValue(settingValue, globalStates)
              : settingValue

          update({ [setting.keyPath]: value })
          if (typeof value !== 'undefined') {
            helperSettingsValue[setting.keyPath] = value
          }
        })
      }
    )

    setHelperSettings(helperSettingsValue)
  }, [menu, surveyHash.refetchHash])

  // Update survey setting function
  const updateSurveySetting = useCallback(
    (setting, value, markAsLastUpdatedSetting = true) => {
      const currentValue = getSettingValueFromSurvey(survey, setting)
      const entity = setting.entity

      // Validate the update
      const validationError = validateSettingUpdate(setting, value, survey)
      if (validationError) {
        return
      }

      // Format the update value
      const updateInfo = formatUpdateValue(setting, value, globalStates)

      // Get the update key
      const updateKey = getUpdateKey(setting, updateInfo)

      // Create and apply the survey update
      const surveyUpdate = createSurveyUpdate(
        setting,
        updateInfo,
        updateKey,
        entity,
        survey
      )
      update(surveyUpdate)

      // Handle helper settings or buffer operations
      if (setting?.helperSetting) {
        setHelperSettings({
          ...helperSettings,
          [setting.keyPath]: updateInfo.updateValue,
        })
      } else {
        const { operation, operationEntity } = createBufferOperationForSetting(
          setting,
          updateInfo,
          updateKey,
          entity,
          survey.sid || survey.id,
          survey,
          createBufferOperation
        )

        addToBuffer(operation)

        // refreshing the survey if it's a survey entity to be updated
        if (operationEntity === Entities.survey) {
          setSurveyRefreshRequired(true)
        }
      }

      // Track the last updated setting
      if (markAsLastUpdatedSetting) {
        setLastUpdatedSettings({
          value: updateInfo.updateValue,
          previousValue: currentValue,
          setting,
          entity,
        })
      }
    },
    [
      survey,
      update,
      globalStates,
      helperSettings,
      setHelperSettings,
      addToBuffer,
      setSurveyRefreshRequired,
      setLastUpdatedSettings,
    ]
  )

  // Linked settings management
  useEffect(() => {
    const setting = lastUpdatedSettings.setting
    const value = lastUpdatedSettings.value
    const previousValue = lastUpdatedSettings.previousValue

    const settingsToRerender = {}

    Object.entries(settingsBlocksInfo?.[menu] || {}).forEach(
      ([, blockSettings]) => {
        Object.values(blockSettings.settings).forEach((linkedSetting) => {
          if (linkedSetting?.linkedSettingsHandler) {
            const isSettingLinked =
              linkedSetting?.linkedSettingsHandler?.linkedSettings
                ?.map((linked) => linked.keyPath)
                ?.includes(setting.keyPath)

            if (!isSettingLinked) {
              return
            }

            if (linkedSetting?.linkedSettingsHandler?.getUpdateValue) {
              const linkedSettingValue =
                linkedSetting?.linkedSettingsHandler?.getUpdateValue(
                  getSettingValueFromSurvey(survey, linkedSetting),
                  previousValue,
                  value,
                  setting,
                  globalStates
                )
              if (linkedSettingValue !== ignoreUpdate) {
                updateSurveySetting(linkedSetting, linkedSettingValue, false)
              }
            }

            if (linkedSetting.linkedSettingsHandler?.rerender) {
              const shouldRerender =
                linkedSetting?.linkedSettingsHandler?.rerender(
                  getSettingValueFromSurvey(survey, linkedSetting),
                  previousValue,
                  value,
                  setting,
                  globalStates
                )

              settingsToRerender[linkedSetting.keyPath] = shouldRerender
            }
          }
        })
      }
    )

    setRerenderSettings({
      ...rerenderSettings,
      ...settingsToRerender,
    })
  }, [lastUpdatedSettings.value, lastUpdatedSettings.setting?.keyPath])

  return (
    <div id="survey-settings" className="survey-settings-panel mt-5">
      <SettingsBlocksRenderer
        menu={menu}
        settingsBlocksInfo={settingsBlocksInfo}
        panelInfo={panelInfo}
        globalStates={globalStates}
        helperSettings={helperSettings}
        survey={survey}
        updateSurveySetting={updateSurveySetting}
        rerenderSettings={rerenderSettings}
        setRerenderSettings={setRerenderSettings}
      />
      {menu === 'advancedOptions' && <AdvancedOptionsSettings surveyId={id} />}
    </div>
  )
}
