import { SURVEY_MENU_TITLES } from 'helpers'
import { SurveySettingBlock } from '../SurveySettingBlock'
import { useMemo } from 'react'
import { PluginSlot } from 'plugins/PluginSlot'
import { PLUGIN_SLOTS } from 'plugins/slots'

/**
 * Component that renders settings blocks for a given menu
 * @param {string} menu - The current menu selection
 * @param {Object} settingsBlocksInfo - The settings blocks configuration
 * @param {Object} panelInfo - Panel information object
 * @param {Object} globalStates - The global states object
 * @param {Object} helperSettings - The helper settings object
 * @param {Object} survey - The survey object
 * @param {Function} updateSurveySetting - Function to update a survey setting
 * @param {Object} rerenderSettings - Current rerender settings state
 * @param {Function} setRerenderSettings - Function to set rerender settings
 */
export const SettingsBlocksRenderer = ({
  menu,
  settingsBlocksInfo,
  panelInfo,
  globalStates,
  helperSettings,
  survey,
  updateSurveySetting,
  rerenderSettings,
  setRerenderSettings,
}) => {
  const blockToRender = useMemo(() => {
    return Object.entries(settingsBlocksInfo?.[menu] || {})
  }, [settingsBlocksInfo, menu])

  return (
    <>
      {blockToRender.map(([blockKey, blockSettingsInfo], blockIndex) => {
        return (
          <div key={`${blockIndex}-${blockKey}-container`}>
            <SurveySettingBlock
              blockSettingsInfo={blockSettingsInfo}
              currentOpenPanelLabel={panelInfo.label}
              globalStates={globalStates}
              helperSettings={helperSettings}
              survey={survey}
              updateSurveySetting={updateSurveySetting}
              key={`${blockIndex}-${blockKey}`}
              rerenderSettings={rerenderSettings}
              setRerenderSettings={setRerenderSettings}
            />
            {menu === SURVEY_MENU_TITLES.tokens && (
              <PluginSlot
                slotName={PLUGIN_SLOTS.SURVEY_SETTINGS_BLOCK_TOKENS_BOTTOM}
              />
            )}
          </div>
        )
      })}
    </>
  )
}
