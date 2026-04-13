import {
  ToggleButtons,
  Input,
  Select,
  CheckboxRadio,
} from 'components/UIComponents'
import {
  Entities,
  getSettingValueFromSurvey,
  ignoreUpdate,
  STATES,
} from 'helpers'

import { getOnOffOptions, ONOFF_BOOLEAN } from 'helpers/options'

export const getPresentationSettingsBlocks = () => ({
  DISPLAY: {
    title: t('Display'),
    settings: {
      SHOW_PROGRESS: {
        keyPath: 'showProgress',
        props: {
          id: 'show-progress-bar',
          mainText: t('Progress bar'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      SHOW_NO_ANSWER: {
        keyPath: 'showNoAnswer',
        props: {
          id: 'show-no-answer',
          mainText: t('No answer'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      SHOW_GROUP_INFO: {
        keyPath: 'showGroupInfo',
        props: {
          id: 'show-group-info',
          groupName: 'show-group-info',
          mainText: t('Group name and description'),
          childComponent: CheckboxRadio,
          optionClassName: '',
          options: [
            { label: t('Group name'), value: 'showGroupName' },
            { label: t('Description'), value: 'showGroupDescription' },
          ],
          noPermissionDisabled: true,
        },
      },
      SHOW_QNUM_CODE_INFO: {
        keyPath: 'showQNumCode',
        props: {
          id: 'show-qnum-code',
          groupName: 'show-qnum-code',
          mainText: t('Question number and code'),
          childComponent: CheckboxRadio,
          optionClassName: '',
          options: [
            { label: t('Question number'), value: 'showNumber' },
            { label: t('Code'), value: 'showCode' },
          ],
          noPermissionDisabled: true,
        },
      },
      NO_KEYBOARD: {
        keyPath: 'noKeyboard',
        props: {
          id: 'no-keyboard',
          mainText: t('On-screen keyboard'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      QUESTION_INDEX: {
        keyPath: 'questionIndex',
        props: {
          id: 'question-index',
          mainText: t('Question index'),
          childComponent: ToggleButtons,
          toggleOptions: [
            { name: t('Enabled'), value: 2 },
            { name: t('Disabled'), value: 0 },
            { name: t('Incremental'), value: 1 },
          ],
          noPermissionDisabled: true,
        },
      },
    },
  },
  LANGUAGE_DISPLAY: {
    title: t('Language specific display'),
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
                : 'No data available',
            }

            return languageOption
          })
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelper =
            helperSettings[
              getPresentationSettingsBlocks().LANGUAGE_DISPLAY.settings
                .LANGUAGE_HELPER.keyPath
            ]

          return languageHelper ? languageHelper : survey.language
        },
        props: {
          id: 'presentation-language',
          mainText: t('Language'),
          childComponent: Select,
          noPermissionDisabled: true,
          childOnNewLine: true,
          dataTestId: 'presentation-language',
        },
      },
      DATE_FORMAT: {
        //DROPDOWN default is based on selected lang...
        entity: Entities.languageSetting,
        keyPath: 'languageSettings.dateFormat',
        props: {
          childComponent: Select,
          childOnNewLine: true,
          mainText: t('Date format'),
          id: 'date-format',
          placeholder: 'DD/MM/YYYY',
          noPermissionDisabled: true,
        },
        selectOptions: (globalStates) => {
          const { dateFormats } = globalStates[STATES.SITE_SETTINGS]
          return Object.keys(dateFormats).map((index) => {
            return {
              value: parseInt(index),
              label: dateFormats[index]['dateformat'],
            }
          })
        },
        formatUpdateValue: (value, globalStates) => {
          const {
            survey: { languageSettings },
          } = globalStates[STATES.SURVEY]

          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPresentationSettingsBlocks().LANGUAGE_DISPLAY.settings
                .LANGUAGE_HELPER.keyPath
            ]

          const updateValue = {
            ...languageSettings,
            [languageHelperValue]: {
              ...languageSettings[languageHelperValue],
              dateFormat: value,
            },
          }

          return {
            updateValue,
            updateValueKey: 'languageSettings',
            operationValue: { dateFormat: value },
            updateOperationKey: languageHelperValue,
          }
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPresentationSettingsBlocks().LANGUAGE_DISPLAY.settings
                .LANGUAGE_HELPER.keyPath
            ]

          return getSettingValueFromSurvey(
            survey,
            getPresentationSettingsBlocks().LANGUAGE_DISPLAY.settings
              .DATE_FORMAT,
            languageHelperValue
          )
        },
      },
      NUMBER_FORMAT: {
        entity: Entities.languageSetting,
        keyPath: 'languageSettings.numberFormat',
        props: {
          id: 'number-format',
          mainText: t('Decimal mark'),
          childComponent: ToggleButtons,
          toggleOptions: [
            { name: t('Dot (.)'), value: 0 },
            { name: t('Comma (,)'), value: 1 },
          ],
          noPermissionDisabled: true,
        },
        formatUpdateValue: (value, globalStates) => {
          const {
            survey: { languageSettings },
          } = globalStates[STATES.SURVEY]

          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPresentationSettingsBlocks().LANGUAGE_DISPLAY.settings
                .LANGUAGE_HELPER.keyPath
            ]

          const updateValue = {
            ...languageSettings,
            [languageHelperValue]: {
              ...languageSettings[languageHelperValue],
              numberFormat: value,
            },
          }

          return {
            updateValue,
            updateValueKey: 'languageSettings',
            operationValue: { numberFormat: value },
            updateOperationKey: languageHelperValue,
          }
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPresentationSettingsBlocks().LANGUAGE_DISPLAY.settings
                .LANGUAGE_HELPER.keyPath
            ]

          return getSettingValueFromSurvey(
            survey,
            getPresentationSettingsBlocks().LANGUAGE_DISPLAY.settings
              .NUMBER_FORMAT,
            languageHelperValue
          )
        },
      },
      ALIAS: {
        entity: Entities.languageSetting,
        keyPath: 'languageSettings.alias',
        props: {
          id: 'alias',
          mainText: t('Survey alias'),
          childOnNewLine: true,
          childComponent: Input,
          noPermissionDisabled: true,
        },
        formatUpdateValue: (value, globalStates) => {
          const {
            survey: { languageSettings },
          } = globalStates[STATES.SURVEY]

          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPresentationSettingsBlocks().LANGUAGE_DISPLAY.settings
                .LANGUAGE_HELPER.keyPath
            ]

          const updateValue = {
            ...languageSettings,
            [languageHelperValue]: {
              ...languageSettings[languageHelperValue],
              alias: value,
            },
          }

          return {
            updateValue,
            updateValueKey: 'languageSettings',
            operationValue: { alias: value },
            updateOperationKey: languageHelperValue,
          }
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const helperSettings = globalStates[STATES.HELPER_SETTINGS]
          const languageHelperValue =
            helperSettings[
              getPresentationSettingsBlocks().LANGUAGE_DISPLAY.settings
                .LANGUAGE_HELPER.keyPath
            ]
          return getSettingValueFromSurvey(
            survey,
            getPresentationSettingsBlocks().LANGUAGE_DISPLAY.settings.ALIAS,
            languageHelperValue
          )
        },
      },
    },
  },
  NAVIGATION: {
    title: t('Navigation'),
    settings: {
      AUTO_REDIRECT: {
        keyPath: 'autoRedirect',
        props: {
          id: 'auto-load-end-url',
          mainText: t('Automatically load end URL when survey complete'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      ALLOW_PREV: {
        keyPath: 'allowPrev',
        props: {
          id: 'allow-backward-navigation',
          mainText: t('Allow backward navigation'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      DELAY_TOGGLE: {
        keyPath: 'delayToggle',
        helperSetting: true,
        props: {
          id: 'delay-toggle',
          mainText: t('Navigation delay'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const delayDuration = getSettingValueFromSurvey(
            survey,
            getPresentationSettingsBlocks().NAVIGATION.settings.NAVIGATION_DELAY
          )

          return delayDuration > 0
        },
      },
      NAVIGATION_DELAY: {
        keyPath: 'navigationDelay',
        props: {
          id: 'navigation-delay',
          mainText: t('Navigation delay duration (in seconds)'),
          childComponent: Input,
          noPermissionDisabled: true,
          type: 'number',
        },
        linkedSettingsHandler: {
          get linkedSettings() {
            return [
              getPresentationSettingsBlocks().NAVIGATION.settings.DELAY_TOGGLE,
            ]
          },
          getUpdateValue: (
            value,
            previousLinkedSettingValue,
            currentLinkedSettingValue
          ) => {
            if (!currentLinkedSettingValue) {
              return 0
            } else if (currentLinkedSettingValue && value === 0) {
              return 10
            }

            return ignoreUpdate
          },
        },
        condition: {
          render: {
            get settings() {
              return [
                getPresentationSettingsBlocks().NAVIGATION.settings
                  .DELAY_TOGGLE,
              ]
            },
            check: ([delayToggle]) => {
              return delayToggle.value
            },
          },
        },
      },
      PRINT_ANSWERS: {
        keyPath: 'printAnswers',
        props: {
          id: 'print-answers',
          mainText: t('Participants may print answers'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
    },
  },
  BLOCK_PUBLIC_STATS: {
    title: t('Public statistics'),
    settings: {
      PUBLIC_STATS: {
        keyPath: 'publicStatistics',
        props: {
          id: 'public-statistics',
          mainText: t('Public statistics'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      PUBLIC_GRAPHS: {
        keyPath: 'publicGraphs',
        props: {
          id: 'public-graphs',
          mainText: t('Show graphs in public statistics'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
    },
  },
})
