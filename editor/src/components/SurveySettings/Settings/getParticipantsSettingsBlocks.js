import { getSettingValueFromSurvey, STATES } from 'helpers'

import { ToggleButtons, Input } from 'components/UIComponents'
import { getOnOffOptions, ONOFF_BOOLEAN } from 'helpers/options'

export const getParticipantsSettingsBlocks = () => ({
  PARTICIPANTS: {
    title: t('Participant settings'),
    settings: {
      ANONYMIZED: {
        keyPath: 'anonymized',
        props: {
          id: 'anonymize-responses',
          mainText: t('Anonymized responses'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          activeDisabled: true,
          noPermissionDisabled: true,
        },
      },
      ENABLE_PARTICIPANT_BASED_RESPONSE_PERSISTENCE: {
        keyPath: 'tokenAnswersPersistence',
        props: {
          id: 'token-answer-persistence',
          mainText: t('Enable participant-based response persistence'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      ALLOWED_IT_AFTER_COMPLETION: {
        keyPath: 'allowedItAfterCompletion',
        props: {
          id: 'allow-multiple-responses',
          mainText: t('Allow multiple responses with the same access code'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      ALLOWED_PUBLIC_REGISTRATION: {
        keyPath: 'allowRegister',
        props: {
          id: 'allow-public-registration',
          mainText: t('Allow public registration'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      HTML_EMAIL: {
        keyPath: 'htmlEmail',
        props: {
          id: 'html-email',
          mainText: t('Use HTML format for participant emails'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      SEND_CONFIRMATION: {
        keyPath: 'sendConfirmation',
        props: {
          id: 'send-confirmation',
          mainText: t('Send confirmation emails'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      SET_ACCESS_CODE_LENGTH: {
        keyPath: 'setAccessCodeLength',
        helperSetting: true,
        props: {
          id: 'setAccessCodeLength',
          mainText: t('Set access code length'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
        formatDisplayValue: (value, globalStates) => {
          const { survey } = globalStates[STATES.SURVEY]
          const tokenLengthValue = getSettingValueFromSurvey(
            survey,
            getParticipantsSettingsBlocks().PARTICIPANTS.settings.TOKEN_LENGTH
          )
          return -1 != tokenLengthValue
        },
      },
      TOKEN_LENGTH: {
        keyPath: 'tokenLength',
        linkedSettingsHandler: {
          get linkedSettings() {
            return [
              getParticipantsSettingsBlocks().PARTICIPANTS.settings
                .SET_ACCESS_CODE_LENGTH,
            ]
          },
          getUpdateValue: (
            value,
            previousLinkedSettingValue,
            currentLinkedSettingValue
          ) => {
            if (!currentLinkedSettingValue) {
              return -1
            } else if (value < 0) {
              return 15
            }
            return value
          },
        },
        condition: {
          update: {
            get settings() {
              return [
                getParticipantsSettingsBlocks().PARTICIPANTS.settings
                  .TOKEN_LENGTH,
                getParticipantsSettingsBlocks().PARTICIPANTS.settings
                  .SET_ACCESS_CODE_LENGTH,
              ]
            },
            check: (updateValue, [, isOn]) => {
              const valid = !isOn.value || updateValue > 4

              return {
                valid,
                errorMessage: valid ? '' : 'Token length must be at least 5',
              }
            },
          },
          render: {
            get settings() {
              return [
                getParticipantsSettingsBlocks().PARTICIPANTS.settings
                  .SET_ACCESS_CODE_LENGTH,
              ]
            },
            check: ([setAccessCodeLength]) => {
              return setAccessCodeLength.value
            },
          },
        },
        props: {
          id: 'token-length',
          type: 'number',
          mainText: t('Access code length'),
          childComponent: Input,
          childOnNewLine: true,
        },
      },
    },
  },
})
