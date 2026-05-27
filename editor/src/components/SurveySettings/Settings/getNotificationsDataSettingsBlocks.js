import { getSettingValueFromSurvey, ignoreUpdate, STATES } from 'helpers'
import { ToggleButtons, Input } from 'components/UIComponents'
import {
  getOnOffOptions,
  getYesNoOptions,
  ONOFF_BOOLEAN,
  YESNO_BOOLEAN,
} from 'helpers/options'

export const getNotificationsDataSettingsBlocks = () => ({
  NOTIFICATIONS_DATA: {
    title: t('Notifications & data'),
    settings: {
      DATESTAMP: {
        keyPath: 'datestamp',
        props: {
          id: 'datestamp',
          mainText: t('Date stamp'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          activeDisabled: true,
          noPermissionDisabled: true,
        },
      },
      IP_ADDR: {
        keyPath: 'ipAddr',
        props: {
          id: 'ipAddr',
          mainText: t('Save IP address'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          activeDisabled: true,
          noPermissionDisabled: true,
        },
      },
      IP_ANONYMIZE: {
        keyPath: 'ipAnonymize',
        props: {
          id: 'ipAnonymize',
          mainText: t('Anonymize IP address'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          activeDisabled: true,
          noPermissionDisabled: true,
        },
      },
      REF_URL: {
        keyPath: 'refUrl',
        props: {
          id: 'refUrl',
          mainText: t('Save referrer URL'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          activeDisabled: true,
          noPermissionDisabled: true,
        },
      },
      SAVE_TIMINGS: {
        keyPath: 'saveTimings',
        props: {
          id: 'saveTimings',
          mainText: t('Save timings'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          activeDisabled: true,
          noPermissionDisabled: true,
        },
      },
      ASSESSMENTS: {
        keyPath: 'assessments',
        props: {
          id: 'assessments',
          mainText: t('Enable assessment mode'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      ALLOW_SAVE: {
        keyPath: 'allowSave',
        props: {
          id: 'allowSave',
          mainText: t('Participant may save and resume later'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      EMAIL_NOTIFICATION_TO_TOGGLER: {
        keyPath: 'emailNotificationToToggler',
        helperSetting: true,
        props: {
          id: 'emailNotificationToToggler',
          mainText: t('Send basic admin notifications'),
          noPermissionDisabled: true,
          childComponent: ToggleButtons,
          toggleOptions: getYesNoOptions(YESNO_BOOLEAN),
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const emailNotificationToValue = getSettingValueFromSurvey(
            survey,
            getNotificationsDataSettingsBlocks().NOTIFICATIONS_DATA.settings
              .EMAIL_NOTIFICATION_TO
          )

          return !!emailNotificationToValue || !!value
        },
      },
      EMAIL_NOTIFICATION_TO: {
        keyPath: 'emailNotificationTo',
        noBorderTop: true,
        props: {
          id: 'emailNotificationTo',
          mainText: t('Basic admin notifications email address'),
          noPermissionDisabled: true,
          childComponent: Input,
          childOnNewLine: true,
        },
        linkedSettingsHandler: {
          get linkedSettings() {
            return [
              getNotificationsDataSettingsBlocks().NOTIFICATIONS_DATA.settings
                .EMAIL_NOTIFICATION_TO_TOGGLER,
            ]
          },
          getUpdateValue: (
            value,
            previousLinkedSettingValue,
            currentLinkedSettingValue
          ) => {
            if (!currentLinkedSettingValue) {
              return ''
            }

            return ignoreUpdate
          },
        },
        condition: {
          render: {
            get settings() {
              return [
                getNotificationsDataSettingsBlocks().NOTIFICATIONS_DATA.settings
                  .EMAIL_NOTIFICATION_TO_TOGGLER,
              ]
            },
            check: ([emailNotificationToToggler]) => {
              return emailNotificationToToggler.value
            },
          },
        },
      },
      EMAIL_RESPONSE_TO_TOGGLER: {
        keyPath: 'emailResponseToToggler',
        helperSetting: true,
        props: {
          id: 'emailResponseToToggler',
          mainText: t('Send detailed admin notifications'),
          noPermissionDisabled: true,
          childComponent: ToggleButtons,
          toggleOptions: getYesNoOptions(YESNO_BOOLEAN),
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const emailResponseTo = getSettingValueFromSurvey(
            survey,
            getNotificationsDataSettingsBlocks().NOTIFICATIONS_DATA.settings
              .EMAIL_RESPONSE_TO
          )

          return !!emailResponseTo || !!value
        },
      },
      EMAIL_RESPONSE_TO: {
        keyPath: 'emailResponseTo',
        noBorderTop: true,
        props: {
          id: 'emailResponseTo',
          mainText: t('Detailed admin notifications email address'),
          activeDisabled: true,
          noPermissionDisabled: true,
          childOnNewLine: true,
          childComponent: Input,
        },
        linkedSettingsHandler: {
          get linkedSettings() {
            return [
              getNotificationsDataSettingsBlocks().NOTIFICATIONS_DATA.settings
                .EMAIL_RESPONSE_TO_TOGGLER,
            ]
          },
          getUpdateValue: (
            value,
            previousLinkedSettingValue,
            currentLinkedSettingValue
          ) => {
            if (!currentLinkedSettingValue) {
              return ''
            }

            return ignoreUpdate
          },
        },
        condition: {
          render: {
            get settings() {
              return [
                getNotificationsDataSettingsBlocks().NOTIFICATIONS_DATA.settings
                  .EMAIL_RESPONSE_TO_TOGGLER,
              ]
            },
            check: ([emailResponseToToggler]) => {
              return emailResponseToToggler.value
            },
          },
        },
      },
      GOOGLE_ANALYTICS_API_KEY_SETTING: {
        keyPath: 'googleAnalyticsApiKeySetting',
        helperSetting: true,
        props: {
          id: 'googleAnalyticsApiKeySetting',
          mainText: t('Google Analytics settings'),
          childComponent: ToggleButtons,
          toggleOptions: [
            { name: t('None'), value: 'N' },
            { name: t('Survey settings'), value: 'Y' },
            { name: t('Global settings'), value: 'G' },
          ],
          defaultValue: 'N',
          noPermissionDisabled: true,
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const googleAnalyticsApiKey = getSettingValueFromSurvey(
            survey,
            getNotificationsDataSettingsBlocks().NOTIFICATIONS_DATA.settings
              .GOOGLE_ANALYTICS_API_KEY
          )

          if (!googleAnalyticsApiKey && value !== 'Y') {
            return 'N'
          } else if (googleAnalyticsApiKey === '9999useGlobal9999') {
            return 'G'
          } else {
            return 'Y'
          }
        },
      },
      GOOGLE_ANALYTICS_API_KEY: {
        keyPath: 'googleAnalyticsApiKey',
        noBorderTop: true,
        props: {
          id: 'googleAnalyticsApiKey',
          mainText: t('Google Analytics Tracking ID'),
          childComponent: Input,
          childOnNewLine: true,
          noPermissionDisabled: true,
        },
        condition: {
          render: {
            get settings() {
              return [
                getNotificationsDataSettingsBlocks().NOTIFICATIONS_DATA.settings
                  .GOOGLE_ANALYTICS_API_KEY_SETTING,
              ]
            },
            check: ([googleAnalyticsApiKeySetting]) => {
              return googleAnalyticsApiKeySetting.value === 'Y'
            },
          },
        },
        linkedSettingsHandler: {
          get linkedSettings() {
            return [
              getNotificationsDataSettingsBlocks().NOTIFICATIONS_DATA.settings
                .GOOGLE_ANALYTICS_API_KEY_SETTING,
            ]
          },
          getUpdateValue: (
            value,
            previousLinkedSettingValue,
            currentLinkedSettingValue
          ) => {
            if (currentLinkedSettingValue === 'N') {
              return ''
            } else if (currentLinkedSettingValue === 'G') {
              return '9999useGlobal9999'
            } else if (currentLinkedSettingValue === 'Y') {
              return value === '9999useGlobal9999' ? '' : value
            }

            return ignoreUpdate
          },
        },
      },
      GOOGLE_ANALYTICS_STYLE: {
        keyPath: 'googleAnalyticsStyle',
        noBorderTop: true,
        props: {
          id: 'googleAnalyticsStyle',
          mainText: t('Google Analytics style'),
          childComponent: ToggleButtons,
          toggleOptions: [
            { name: t('Off'), value: 0 },
            { name: t('Default'), value: 1 },
            { name: t('Survey-SID/Group'), value: 2 },
          ],
          noPermissionDisabled: true,
        },
        condition: {
          render: {
            get settings() {
              return [
                getNotificationsDataSettingsBlocks().NOTIFICATIONS_DATA.settings
                  .GOOGLE_ANALYTICS_API_KEY_SETTING,
              ]
            },
            check: ([googleAnalyticsApiKeySetting]) => {
              return (
                googleAnalyticsApiKeySetting.value === 'Y' ||
                googleAnalyticsApiKeySetting.value === 'G'
              )
            },
          },
        },
      },
    },
  },
})
