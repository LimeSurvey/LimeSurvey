import {
  PAGES,
  SURVEY_MENU_TITLES,
  decodeHTMLEntities,
  getSiteUrl,
} from 'helpers'
import { getTooltipMessages } from 'helpers/options'

export const presentationOptions = (surveyId, menuEntries) => [
  {
    label: menuEntries[SURVEY_MENU_TITLES.themeOptions]?.menuTitle,
    labelEditor: t('Theme options'),
    menu: SURVEY_MENU_TITLES.themeOptions,
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.presentation]?.menuTitle,
    labelEditor: t('Presentation'),
    menu: SURVEY_MENU_TITLES.presentation,
  },
]

export const settingsOptions = (surveyId, menuEntries) => [
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

export const menuOptions = (surveyId, menuEntries, isSurveyActive) => [
  // {
  //   label: menuEntries[SURVEY_MENU_TITLES.overview]?.menuTitle,
  //   labelEditor: t('Overview'),
  //   menu: SURVEY_MENU_TITLES.overview,
  //   redirect: getSiteUrl(
  //     '/questionAdministration/listQuestions?surveyid=' + surveyId
  //   ),
  // },
  {
    label: menuEntries[SURVEY_MENU_TITLES.participants]?.menuTitle,
    labelEditor: t('Participants'),
    menu: SURVEY_MENU_TITLES.participants,
    redirect: getSiteUrl('/admin/tokens/sa/index/surveyid/' + surveyId),
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.emailTemplates]?.menuTitle,
    labelEditor: t('Email templates'),
    menu: SURVEY_MENU_TITLES.emailTemplates,
    redirect: getSiteUrl('/admin/emailtemplates/sa/index/surveyid/' + surveyId),
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.failedEmail]?.menuTitle,
    labelEditor: t('Failed email notifications'),
    menu: SURVEY_MENU_TITLES.failedEmail,
    redirect: getSiteUrl('/failedEmail/index?surveyid=' + surveyId),
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.quotas]?.menuTitle,
    labelEditor: t('Quotas'),
    menu: SURVEY_MENU_TITLES.quotas,
    redirect: getSiteUrl('/quotas/index?surveyid=' + surveyId),
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.assessments]?.menuTitle,
    labelEditor: t('Assessments'),
    menu: SURVEY_MENU_TITLES.assessments,
    redirect: getSiteUrl('/assessment/index?surveyid=' + surveyId),
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.panelIntegration]?.menuTitle,
    labelEditor: t('Panel integration'),
    menu: SURVEY_MENU_TITLES.panelIntegration,
    redirect: getSiteUrl(
      '/surveyAdministration/rendersidemenulink?surveyid=' +
        surveyId +
        '&subaction=panelintegration'
    ),
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.responses]?.menuTitle,
    labelEditor: t('Responses'),
    menu: 'responses',
    panel: '',
    page: PAGES.RESPONSES,
    disabled: !isSurveyActive,
    disabledTip: getTooltipMessages().SURVEY_NOT_ACTIVE,
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.statistics]?.menuTitle,
    labelEditor: t('Statistics'),
    menu: SURVEY_MENU_TITLES.statistics,
    redirect: getSiteUrl('/admin/statistics?sa=index&surveyid=' + surveyId),
    disabled: !isSurveyActive,
    disabledTip: getTooltipMessages().SURVEY_NOT_ACTIVE,
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.resources]?.menuTitle,
    labelEditor: t('Resources'),
    menu: SURVEY_MENU_TITLES.resources,
    redirect: getSiteUrl(
      '/surveyAdministration/rendersidemenulink?surveyid=' +
        surveyId +
        '&subaction=resources'
    ),
  },
  {
    label: menuEntries[SURVEY_MENU_TITLES.plugins]?.menuTitle,
    labelEditor: t('Simple plugins'),
    menu: SURVEY_MENU_TITLES.plugins,
    redirect: getSiteUrl(
      '/surveyAdministration/rendersidemenulink?surveyid=' +
        surveyId +
        '&subaction=plugins'
    ),
  },
]
