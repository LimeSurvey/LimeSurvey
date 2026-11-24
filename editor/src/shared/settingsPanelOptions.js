import { decodeHTMLEntities, SURVEY_MENU_TITLES } from 'helpers';

export const settingsPanelOptions = (surveyId, menuEntries) => [
  {
    label: menuEntries[SURVEY_MENU_TITLES.generalSettings]?.menuTitle,
    labelEditor: t('General'),
    menu: SURVEY_MENU_TITLES.generalSettings,
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.dataSecurity]?.menuTitle,
    labelEditor: t('Privacy policy'),
    menu: SURVEY_MENU_TITLES.dataSecurity,
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.tokens]?.menuTitle,
    labelEditor: t('Participant settings'),
    menu: SURVEY_MENU_TITLES.tokens,
  },
  {
    label: decodeHTMLEntities(
      menuEntries[SURVEY_MENU_TITLES.publication]?.menuTitle
    ),
    labelEditor: t('Publication & access'),
    menu: SURVEY_MENU_TITLES.publication,
  },
  {
    label: decodeHTMLEntities(
      menuEntries[SURVEY_MENU_TITLES.notification]?.menuTitle
    ),
    labelEditor: t('Notifications & data'),
    menu: SURVEY_MENU_TITLES.notification,
  },
]