import { PAGES, SURVEY_MENU_TITLES, getTooltipMessages } from 'helpers'
import { getSharingPanels } from 'shared/getSharingPanels'

export const getTopBarDropdownItems = ({
  surveyId,
  surveySid,
  isSurveyActive,
  handleStartEditorTutorial,
}) => {
  return [
    {
      type: 'header',
      label: t('Navigate'),
    },
    {
      type: 'item',
      label: t('Workspace'),
      icon: 'ri-microsoft-fill',
      url: '/dashboard/view',
    },
    {
      type: 'item',
      label: t('Editor'),
      icon: 'ri-bar-chart-horizontal-line',
      url: `#/${PAGES.EDITOR}/${surveyId}/structure`,
    },
    {
      type: 'item',
      label: t('Share'),
      icon: 'ri-share-forward-line',
      url: `#/${PAGES.SHARE}/${surveyId}/${getSharingPanels().sharing.panel}/${SURVEY_MENU_TITLES.sharingOverview}`,
    },
    {
      type: 'item',
      label: t('Analyze'),
      icon: 'ri-bar-chart-2-line',
      url: `#/responses/${surveySid}`,
      disabled: {
        state: !isSurveyActive,
        tooltip: getTooltipMessages().SURVEY_NOT_ACTIVE_NO_RESULTS,
      },
    },
    {
      type: 'divider',
    },
    {
      type: 'header',
      label: t('Help'),
    },
    {
      type: 'item',
      label: t('Interactive help'),
      icon: 'ri-information-line',
      onClick: handleStartEditorTutorial,
      disabled: {
        state: isSurveyActive,
        tooltip: getTooltipMessages().DISABLE_FIRST,
      },
    },
    // {
    //   type: 'item',
    //   label: t('Fullscreen preview'),
    // },
    // {
    //   type: 'item',
    //   label: t('Question codes'),
    // },
    // {
    //   type: 'divider'
    // },
    // {
    //   type: 'header',
    //   label: t('Tools'),
    // },
    // {
    //   type: 'item',
    //   label: t('Import/export'),
    // },
    // {
    //   type: 'item',
    //   label: t('Check logic'),
    // },
    // {
    //   type: 'item',
    //   label: t('Keyboard shortcuts'),
    // },
    // {
    //   type: 'item',
    //   label: t('Welcome guide'),
    // },
    // {
    //   type: 'item',
    //   label: t('Help center'),
    // }
  ]
}
