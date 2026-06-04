import * as General from './getGeneralSettingsBlocks'
import * as Participants from './getParticipantsSettingsBlocks'
import * as Presentation from './getPresentationSettingsBlocks'
import * as PrivacyPolicy from './getPrivacyPolicySettingsBlocks'
import * as PublicationAccess from 'shared/getPublicationAccessSettingsBlocks'
import * as NotificationData from './getNotificationsDataSettingsBlocks'
import * as ThemeOptions from './getThemeOptionsSettingsBlocks'

export const settingsBlocks = {
  ...General,
  ...Participants,
  ...Presentation,
  ...PrivacyPolicy,
  ...PublicationAccess,
  ...NotificationData,
  ...ThemeOptions,
}
