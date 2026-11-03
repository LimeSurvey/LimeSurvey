import { DateTimePickerComponent, ToggleButtons } from 'components/UIComponents'
import { getOnOffOptions, ONOFF_BOOLEAN } from 'helpers/options'
import getSiteUrl from 'helpers/getSiteUrl'

export const getPublicationAccessSettingsBlocks = () => ({
  DATE: {
    title: t('Publication date'),
    settings: {
      START_DATE: {
        keyPath: 'startDate',
        props: {
          id: 'start-date-time',
          mainText: t('Start date/time'),
          childComponent: DateTimePickerComponent,
          childOnNewLine: true,
          noPermissionDisabled: true,
        },
      },
      EXPIRES: {
        keyPath: 'expires',
        props: {
          id: ' end-date-time',
          mainText: t('End date/time'),
          childComponent: DateTimePickerComponent,
          noPermissionDisabled: true,
          childOnNewLine: true,
        },
      },
    },
  },
  ACCESS: {
    settings: {
      LIST_PUBLIC: {
        keyPath: 'listPublic',
        props: {
          id: 'listPublic',
          mainText: t('Link survey on %spublic index page%s'),
          link: getSiteUrl(),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      USE_COOKIE: {
        keyPath: 'useCookie',
        props: {
          id: 'useCookie',
          mainText: t('Set cookie to prevent repeated participation'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      USE_CAPTCHA_ACCESS: {
        keyPath: 'useCaptchaAccess',
        props: {
          id: 'useCaptchaAccess',
          mainText: t('Use CAPTCHA for survey access'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      USE_CAPTCHA_REGISTRATION: {
        keyPath: 'useCaptchaRegistration',
        props: {
          id: 'useCaptchaRegistration',
          mainText: t('Use CAPTCHA for registration'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
      USE_CAPTCHA_SAVE_LOAD: {
        keyPath: 'useCaptchaSaveLoad',
        props: {
          id: 'useCaptchaSaveLoad',
          mainText: t('Use CAPTCHA for save and load'),
          childComponent: ToggleButtons,
          toggleOptions: getOnOffOptions(ONOFF_BOOLEAN),
          noPermissionDisabled: true,
        },
      },
    },
  },
})
