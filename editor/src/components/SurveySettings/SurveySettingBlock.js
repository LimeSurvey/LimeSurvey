import { decodeHTMLEntities } from 'helpers'

import { SettingItem } from './SettingItem'
import {
  checkRenderCondition,
  applyDisableCondition,
  getSettingValue,
  formatSettingValue,
  getSettingPreviewUrl,
  getSettingComponent,
} from './utils/settingHelpers'

export const SurveySettingBlock = ({
  blockSettingsInfo,
  currentOpenPanelLabel,
  globalStates,
  helperSettings,
  survey: { active = false, sid = '' },
  survey,
  updateSurveySetting,
  rerenderSettings,
  setRerenderSettings,
}) => {
  return (
    <div className="settings-block p-4 mb-4 bg-white">
      <div className="d-flex justify-content-between">
        <h5>
          {decodeHTMLEntities(blockSettingsInfo.title || currentOpenPanelLabel)}
        </h5>
      </div>
      {Object.entries(blockSettingsInfo.settings ?? {}).map(
        ([settingKey, setting]) => {
          // Skip blockTitle in settings loop
          if (settingKey === 'blockTitle') {
            return null
          }

          // Check for access (if the settings menu item is listed in accessibleSettingItems array)
          const noAccessDisabled = false
          const shouldRender = checkRenderCondition(
            setting,
            helperSettings,
            survey,
            globalStates
          )

          // Check render condition
          if (!shouldRender) {
            return null
          }

          // Apply disable condition
          applyDisableCondition(setting, globalStates)

          // Get and format values
          const value = getSettingValue(setting, helperSettings, survey)
          const formattedDisplayValue = formatSettingValue(
            setting,
            value,
            globalStates
          )
          const previewUrl = getSettingPreviewUrl(setting, value, globalStates)

          // Get component
          const Component = getSettingComponent(setting)

          return (
            <SettingItem
              key={settingKey}
              settingKey={settingKey}
              setting={setting}
              formattedValue={formattedDisplayValue}
              previewUrl={previewUrl}
              component={Component}
              active={active}
              sid={sid}
              updateSurveySetting={updateSurveySetting}
              rerenderSettings={rerenderSettings}
              setRerenderSettings={setRerenderSettings}
              globalStates={globalStates}
              noAccessDisabled={noAccessDisabled}
            />
          )
        }
      )}
    </div>
  )
}
