import { SURVEY_MENU_TITLES } from 'helpers'
import { settingsBlocks } from '../Settings'
import { getThemeOptions } from '../Settings/getThemeOptionsSettingsBlocks'

/**
 * Gets the settings blocks configuration for all menu types
 * @returns {Object} - Object mapping menu titles to their settings blocks
 */
export const getSettingsBlocksInfo = (survey) => {
  return {
    [SURVEY_MENU_TITLES.generalSettings]: Object.values(
      settingsBlocks.getGeneralSettingsBlocks()
    ),
    [SURVEY_MENU_TITLES.dataSecurity]: Object.values(
      settingsBlocks.getPrivacyPolicySettingsBlocks()
    ),
    [SURVEY_MENU_TITLES.publication]: Object.values(
      settingsBlocks.getPublicationAccessSettingsBlocks()
    ),
    [SURVEY_MENU_TITLES.notification]: Object.values(
      settingsBlocks.getNotificationsDataSettingsBlocks()
    ),
    [SURVEY_MENU_TITLES.tokens]: Object.values(
      settingsBlocks.getParticipantsSettingsBlocks()
    ),
    [SURVEY_MENU_TITLES.presentation]: Object.values(
      settingsBlocks.getPresentationSettingsBlocks()
    ),
    [SURVEY_MENU_TITLES.themeOptions]: Object.values(getThemeOptions(survey)),
  }
}
