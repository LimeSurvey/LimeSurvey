import { Input, Select } from 'components/UIComponents'
import { STATES, themeOptions } from 'helpers'
import { getFormatOptions } from 'helpers/options'

import { Theme } from '../GeneralSettings/Theme'

export const getGeneralSettingsBlocks = () => ({
  GENERAL: {
    title: t('General'),
    settings: {
      LANGUAGES: {
        keyPath: 'additionalLanguages',
        formatUpdateValue: (languagesArray, globalStates) => {
          const { language } = globalStates[STATES.SURVEY].survey

          const languagesArrayWithoutBaseLanguage = languagesArray.filter(
            (lang) => lang !== language
          )

          const additonalLanguagesString =
            languagesArrayWithoutBaseLanguage.join(' ')

          return {
            updateValue: additonalLanguagesString,
            operationValue: languagesArrayWithoutBaseLanguage,
          }
        },
        condition: {
          update: {
            get settings() {
              return [getGeneralSettingsBlocks().GENERAL.settings.BASE_LANGUAGE]
            },
            check: (updateValue, [baseLanguage]) => {
              const baseLanguageValue = baseLanguage.value
              let baseLanguageExist = false

              updateValue.map((language) => {
                if (language === baseLanguageValue) {
                  baseLanguageExist = true
                }
              })

              return {
                valid: baseLanguageExist,
                errorMessage: baseLanguageExist
                  ? ''
                  : t(
                      'You cannot delete the base language. Please select a different language as base language, first.'
                    ),
              }
            },
          },
        },
        selectOptions: (globalStates) => {
          const allLanguages =
            globalStates[STATES.ALL_AVAILABLE_LANGUAGES] || {}
          const userLang = globalStates[STATES.USER_DETAIL]?.lang
          const languages = allLanguages[userLang] || {}

          return Object.keys(languages).map((key) => {
            return {
              value: key,
              label: languages
                ? languages[key].description
                : t('No data available'),
            }
          })
        },
        formatDisplayValue: (additonalLanguages, globalStates) => {
          const allLanguages = globalStates[STATES.ALL_AVAILABLE_LANGUAGES]
          const languages = allLanguages[globalStates[STATES.USER_DETAIL].lang]
          const { language } = globalStates[STATES.SURVEY].survey

          // Removing the base language from the list of additional languages if it exists to avoid duplication.
          // And sort alphabetically.
          additonalLanguages = additonalLanguages
            .replace(language, '')
            .trim()
            .split(' ')
            .sort()
            .join(' ')

          const selectedLanguages = language
            .concat(additonalLanguages ? ` ${additonalLanguages}` : '')
            .split(' ')

          const languagesOptions = selectedLanguages.map((language) => {
            return {
              value: language,
              label: languages
                ? languages[language]?.description
                : t('No data available'),
            }
          })

          const sortedOptions = [
            languagesOptions[0],
            ...languagesOptions
              .slice(1)
              .sort((a, b) => a.label?.localeCompare(b.label)),
          ]

          return sortedOptions
        },
        linkedSettingsHandler: {
          get linkedSettings() {
            return [getGeneralSettingsBlocks().GENERAL.settings.BASE_LANGUAGE]
          },
          getUpdateValue: (currentValue, previousLinkedSettingValue) => {
            const languagesArray = currentValue
              .concat(` ${previousLinkedSettingValue}`)
              .split(' ')

            return languagesArray
          },
        },
        props: {
          id: 'multiLanguages',
          mainText: t('Survey languages'),
          childComponent: Select,
          noPermissionDisabled: true,
          childOnNewLine: true,
          isMultiselect: true,
          dataTestId: 'additionalLanguages',
        },
      },
      BASE_LANGUAGE: {
        keyPath: 'language',
        selectOptions: (globalStates) => {
          const allLanguages =
            globalStates[STATES.ALL_AVAILABLE_LANGUAGES] || {}
          const languages = allLanguages[globalStates[STATES.USER_DETAIL].lang]
          const { survey } = globalStates[STATES.SURVEY]

          const surveyLanguages = survey.language
            .concat(` ${survey.additionalLanguages}`)
            .trim()
            .split(' ')

          return surveyLanguages.map((option) => {
            let languageOption = {
              value: option,
              label: languages
                ? languages[option]?.description
                : t('No data available'),
            }

            return languageOption
          })
        },
        props: {
          id: 'baseLanguage',
          mainText: t('Base language'),
          childComponent: Select,
          noPermissionDisabled: true,
          childOnNewLine: true,
          dataTestId: 'baseLanguage',
        },
      },
      OWNER_ID: {
        keyPath: 'ownerId',
        props: {
          id: 'ownerId',
          mainText: t('Survey owner'),
          childComponent: Select,
          childOnNewLine: true,
          noPermissionDisabled: true,
        },
        selectOptions: (globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]

          return survey?.ownersList?.map((owners) => ({
            value: parseInt(owners.value),
            label: owners.label,
          }))
        },
      },
      ADMIN: {
        keyPath: 'admin',
        props: {
          id: 'admin',
          mainText: t('Administrator'),
          dataTestId: '',
          childComponent: Input,
          childOnNewLine: true,
          noPermissionDisabled: true,
        },
      },
      ADMIN_EMAIL: {
        keyPath: 'adminEmail',
        props: {
          id: 'adminEmail',
          mainText: t('Administrator email address'),
          dataTestId: '',
          childComponent: Input,
          childOnNewLine: true,
          noPermissionDisabled: true,
        },
      },
      BOUNCE_EMAIL: {
        keyPath: 'bounceEmail',
        props: {
          id: 'bounceEmail',
          mainText: t('Bounce email address'),
          dataTestId: '',
          childComponent: Input,
          childOnNewLine: true,
          noPermissionDisabled: true,
        },
      },
      GSID: {
        keyPath: 'gsid',
        props: {
          id: 'gsid',
          mainText: t('Group'),
          dataTestId: '',
          childComponent: Select,
          childOnNewLine: true,
          noPermissionDisabled: true,
        },
        selectOptions: (globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]

          return Object.keys(survey?.groupsList)?.map((key) => ({
            label: survey?.groupsList[parseInt(key)],
            value: parseInt(key),
          }))
        },
      },
      FORMAT: {
        keyPath: 'format',
        props: {
          id: 'format',
          mainText: t('Format'),
          dataTestId: '',
          childComponent: Select,
          childOnNewLine: true,
          noPermissionDisabled: true,
        },
        selectOptions: (globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const inheritLabel = t('Inherit')
          return process.env.REACT_APP_DEV_MODE
            ? [
                {
                  label:
                    inheritLabel +
                    ` [ ${
                      getFormatOptions().find(
                        (option) => option.value === survey.formatInherited
                      )?.label
                    } ]`,
                  value: 'I',
                },
                ...getFormatOptions(),
              ]
            : getFormatOptions()
        },
      },
    },
  },
  THEME: {
    title: t('Theme'),
    settings: {
      TEMPLATE: {
        component: Theme,
        keyPath: 'template',
        props: {
          id: 'template',
          mainText: t('Theme'),
          dataTestId: '',
          childComponent: Select,
          childOnNewLine: true,
          noPermissionDisabled: true,
        },
        selectOptions: (globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]

          return process.env.REACT_APP_DEV_MODE
            ? [
                {
                  label: t('Inherit') + ` [ ${survey.templateInherited} ]`,
                  value: 'inherit',
                },
                ...themeOptions,
              ]
            : themeOptions
        },
      },
    },
  },
})
