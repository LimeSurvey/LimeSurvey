import { Entities, getSettingValueFromSurvey, STATES } from 'helpers'
import { getOnOffOptions, ONOFF_BOOLEAN } from 'helpers/options'

import {
  ContentEditor,
  Select,
  ToggleButtons,
  Input,
} from 'components/UIComponents'

export const getPrivacyPolicySettingsBlocks = () => ({
  TOGGLES: {
    settings: {
      SHOW_SURVEY_POLICY_NOTICE: {
        keyPath: 'showSurveyPolicyNotice',
        props: {
          id: 'show-survey-policy-notice',
          mainText: t('Show privacy policy text with mandatory checkbox'),
          childComponent: ToggleButtons,
          toggleOptions: [
            { name: t('No'), value: 0 },
            { name: t('Inline'), value: 1 },
            { name: t('Collapsible'), value: 2 },
          ],
          noPermissionDisabled: true,
        },
      },
      SHOW_LEGAL_NOTICE_BUTTON: {
        keyPath: 'showLegalNoticeButton',
        renderExistCondition: 'showLegalNoticeButton',
        props: {
          id: 'show-legal-notice',
          mainText: t('Show link to legal notice in survey'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
        condition: {
          render: {
            get settings() {
              return [
                getPrivacyPolicySettingsBlocks().TOGGLES.settings
                  .SHOW_LEGAL_NOTICE_BUTTON,
              ]
            },
            check: ([legalNoticeButton], globalStates) => {
              const { survey } = globalStates[STATES.SURVEY]
              // only show when prop is in survey obj (CE doesn't have this)
              return Object.hasOwn(survey, legalNoticeButton.setting.keyPath)
            },
          },
        },
      },
      SHOW_DATA_POLICY_BUTTON: {
        keyPath: 'showDataPolicyButton',
        renderExistCondition: 'showDataPolicyButton',
        props: {
          id: 'show-privacy-policy',
          mainText: t('Show link to data policy in survey'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
        condition: {
          render: {
            get settings() {
              return [
                getPrivacyPolicySettingsBlocks().TOGGLES.settings
                  .SHOW_DATA_POLICY_BUTTON,
              ]
            },
            check: ([policyButton], globalStates) => {
              const { survey } = globalStates[STATES.SURVEY]
              // only show when prop is in survey obj (CE doesn't have this)
              return Object.hasOwn(survey, policyButton.setting.keyPath)
            },
          },
        },
      },
    },
  },
  TEXTS: {
    title: t('Languages and texts'),
    settings: {
      LANGUAGE_HELPER: {
        keyPath: 'languageHelper',
        helperSetting: true,
        selectOptions: (globalStates) => {
          const allLanguages = globalStates[STATES.ALL_AVAILABLE_LANGUAGES]
          const languages = allLanguages[globalStates[STATES.USER_DETAIL].lang]
          const { survey } = globalStates[STATES.SURVEY]

          const surveyLanguages = [survey.language].concat(
            survey.additionalLanguages
              ? survey.additionalLanguages.split(' ')
              : []
          )

          return surveyLanguages.map((option) => {
            let addOn =
              option === survey.language ? ' (' + t('Base language') + ')' : ''
            let languageOption = {
              value: option,
              label: languages
                ? languages[option]?.description + addOn
                : t('No data available'),
            }

            return languageOption
          })
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelper =
            helperSettings[
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER
                .keyPath
            ]

          return languageHelper ? languageHelper : survey.language
        },
        props: {
          id: 'datasecurity-language',
          mainText: t('Language'),
          childComponent: Select,
          noPermissionDisabled: true,
          childOnNewLine: true,
          dataTestId: 'datasecurity-language',
        },
      },
      LEGAL_NOTICE: {
        entity: Entities.languageSetting,
        keyPath: 'languageSettings.legalNotice',
        renderExistCondition: 'showLegalNoticeButton',
        props: {
          childComponent: ContentEditor,
          childOnNewLine: true,
          extraClass: 'editable-content-editor textarea',
          id: 'legal-notice-message',
          mainText: t('Legal notice message'),
          placeholder: t('Legal notice message'),
          noPermissionDisabled: true,
          showToolbar: true,
        },
        condition: {
          render: {
            get settings() {
              return [
                getPrivacyPolicySettingsBlocks().TOGGLES.settings
                  .SHOW_LEGAL_NOTICE_BUTTON,
              ]
            },
            check: ([showLegalNoticeButton]) => {
              // only show when showLegalNoticeButton is enabled
              return showLegalNoticeButton.value
            },
          },
        },
        formatUpdateValue: (value, globalStates) => {
          const {
            survey: { languageSettings },
          } = globalStates[STATES.SURVEY]

          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER
                .keyPath
            ]

          const updateValue = {
            ...languageSettings,
            [languageHelperValue]: {
              ...languageSettings[languageHelperValue],
              legalNotice: value,
            },
          }

          return {
            updateValue,
            updateValueKey: 'languageSettings',
            operationValue: { legalNotice: value },
            updateOperationKey: languageHelperValue,
          }
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER
                .keyPath
            ]
          const settingValue = getSettingValueFromSurvey(
            survey,
            getPrivacyPolicySettingsBlocks().TEXTS.settings.LEGAL_NOTICE,
            languageHelperValue
          )

          return settingValue
        },
      },
      POLICY_NOTICE: {
        entity: Entities.languageSetting,
        keyPath: 'languageSettings.policyNotice',
        props: {
          childComponent: ContentEditor,
          childOnNewLine: true,
          mainText: t('Privacy policy message'),
          id: 'privacy-policy-message',
          placeholder: t('Privacy policy message'),
          extraClass: 'editable-content-editor form-control textarea',
          noPermissionDisabled: true,
          showToolbar: true,
        },
        formatUpdateValue: (value, globalStates) => {
          const {
            survey: { languageSettings },
          } = globalStates[STATES.SURVEY]

          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER
                .keyPath
            ]

          const updateValue = {
            ...languageSettings,
            [languageHelperValue]: {
              ...languageSettings[languageHelperValue],
              policyNotice: value,
            },
          }

          return {
            updateValue,
            updateValueKey: 'languageSettings',
            operationValue: { policyNotice: value },
            updateOperationKey: languageHelperValue,
          }
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER
                .keyPath
            ]
          const settingValue = getSettingValueFromSurvey(
            survey,
            getPrivacyPolicySettingsBlocks().TEXTS.settings.POLICY_NOTICE,
            languageHelperValue
          )

          return settingValue
        },
        linkedSettingsHandler: {
          get linkedSettings() {
            return [
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER,
            ]
          },
          rerender: (
            currentValue,
            previousLinkedSettingValue,
            currentLinkedSettingValue
          ) => {
            return previousLinkedSettingValue !== currentLinkedSettingValue
          },
        },
      },
      POLICY_ERROR: {
        entity: Entities.languageSetting,
        keyPath: 'languageSettings.policyError',
        props: {
          childComponent: ContentEditor,
          childOnNewLine: true,
          mainText: t('Privacy policy error message'),
          id: 'privacy-policy-message',
          placeholder: t('Privacy policy error message'),
          extraClass: 'editable-content-editor form-control textarea',
          noPermissionDisabled: true,
          showToolbar: true,
        },
        formatUpdateValue: (value, globalStates) => {
          const {
            survey: { languageSettings },
          } = globalStates[STATES.SURVEY]

          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER
                .keyPath
            ]

          const updateValue = {
            ...languageSettings,
            [languageHelperValue]: {
              ...languageSettings[languageHelperValue],
              policyError: value,
            },
          }

          return {
            updateValue,
            updateValueKey: 'languageSettings',
            operationValue: { policyError: value },
            updateOperationKey: languageHelperValue,
          }
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER
                .keyPath
            ]
          const settingValue = getSettingValueFromSurvey(
            survey,
            getPrivacyPolicySettingsBlocks().TEXTS.settings.POLICY_ERROR,
            languageHelperValue
          )

          return settingValue
        },
        linkedSettingsHandler: {
          get linkedSettings() {
            return [
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER,
            ]
          },
          rerender: (currentValue, previousLinkedSettingValue, value) => {
            return previousLinkedSettingValue !== value
          },
        },
      },
      POLICY_NOTICE_LABEL: {
        entity: Entities.languageSetting,
        keyPath: 'languageSettings.policyNoticeLabel',
        props: {
          childComponent: Input,
          childOnNewLine: true,
          mainText: t('Privacy policy checkbox label'),
          id: 'privacy-policy-label-message',
          placeholder: t('Privacy policy checkbox label'),
          extraClass: 'editable-content-editor',
          noPermissionDisabled: true,
        },
        formatUpdateValue: (value, globalStates) => {
          const {
            survey: { languageSettings },
          } = globalStates[STATES.SURVEY]

          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER
                .keyPath
            ]

          const updateValue = {
            ...languageSettings,
            [languageHelperValue]: {
              ...languageSettings[languageHelperValue],
              policyNoticeLabel: value,
            },
          }

          return {
            updateValue,
            updateValueKey: 'languageSettings',
            operationValue: { policyNoticeLabel: value },
            updateOperationKey: languageHelperValue,
          }
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER
                .keyPath
            ]
          const settingValue = getSettingValueFromSurvey(
            survey,
            getPrivacyPolicySettingsBlocks().TEXTS.settings.POLICY_NOTICE_LABEL,
            languageHelperValue
          )

          return settingValue
        },
        linkedSettingsHandler: {
          get linkedSettings() {
            return [
              getPrivacyPolicySettingsBlocks().TEXTS.settings.LANGUAGE_HELPER,
            ]
          },
          rerender: (currentValue, previousLinkedSettingValue, value) => {
            return previousLinkedSettingValue !== value
          },
        },
      },
    },
  },
})
