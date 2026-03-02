import { useLocation } from 'react-router-dom'
import classNames from 'classnames'

import { useAppState } from 'hooks'
import {
  getSiteUrl,
  getTooltipMessages,
  PAGES,
  STATES,
  SURVEY_MENU_TITLES,
} from 'helpers'
import { Dropdown } from 'components/UIComponents/Dropdown/Dropdown'
import { EyeIcon } from 'components/icons'
import { getSharingPanels } from 'shared/getSharingPanels'
import { PLUGIN_SLOTS } from 'plugins/slots'
import { PluginSlot } from 'plugins/PluginSlot'

import { ActionButton } from './Button/ActionButton'
import { TopBarQuestionInserter } from './TopBarQuestionInserter'

export const TopBarActions = ({
  surveyId,
  showPreviewButton,
  showShareButton,
  isSurveyActive,
  survey,
  operationsLength,
  operationsBuffer,
  saveState,
  showShareActionButton,
  showExportResponsesButton,
  showExportStatisticsButton,
  showPublishSettings,
  triggerPublish,
  isAddingQuestionOrGroup,
  setShowOverviewModalRef,
}) => {
  const location = useLocation()
  const [, startEditorTutorial] = useAppState(
    STATES.START_EDITOR_TUTORIAL,
    false
  )

  const handleStartEditorTutorial = () => {
    startEditorTutorial(true)
  }

  const isActiveMenuItem = (url) => {
    if (!url) return false
    const currentPath = (
      window.location.hash ||
      location.pathname ||
      ''
    ).replace('#', '')
    const cleanURL = url.replace('#', '')
    return currentPath.startsWith(cleanURL)
  }

  const dropdownMenuItems = [
    {
      type: 'header',
      label: t('Navigate'),
    },
    {
      type: 'item',
      label: t('Workspace'),
      icon: 'ri-microsoft-fill',
      url: '/dashboard/view',
      disabled: {
        state: isActiveMenuItem('/dashboard/view'),
        tooltip: t('Current page'),
      },
    },
    {
      type: 'item',
      label: t('Editor'),
      icon: 'ri-bar-chart-horizontal-line',
      url: `#/${PAGES.EDITOR}/${surveyId}/structure`,
      disabled: {
        state: isActiveMenuItem(`/${PAGES.EDITOR}/${surveyId}`),
        tooltip: t('Current page'),
      },
    },
    {
      type: 'item',
      label: t('Share'),
      icon: 'ri-share-forward-line',
      url: `#/${PAGES.SHARE}/${surveyId}/${getSharingPanels().sharing.panel}/${SURVEY_MENU_TITLES.sharingOverview}`,
      disabled: {
        state: isActiveMenuItem(`/${PAGES.SHARE}/${surveyId}`),
        tooltip: t('Current page'),
      },
    },
    {
      type: 'item',
      label: t('Results'),
      icon: 'ri-bar-chart-2-line',
      url: `#/responses/${survey.sid}`,
      disabled: {
        state: !isSurveyActive || isActiveMenuItem(`/responses/${survey.sid}`),
        tooltip: !isSurveyActive
          ? getTooltipMessages().SURVEY_NOT_ACTIVE_NO_RESULTS
          : t('Current page'),
      },
    },
    {
      type: 'divider',
    },
    {
      type: 'header',
      label: t('Tools'),
    },
    {
      type: 'submenu',
      label: t('Export'),
      submenu: [
        {
          type: 'item',
          label: t('Survey structure (.lss)'),
          url: getSiteUrl(
            `/admin/export/sa/survey/action/exportstructurexml/surveyid/${survey.sid}`
          ),
        },
        {
          type: 'item',
          label: t('Survey archive'),
          url: getSiteUrl(
            `/admin/export/sa/survey/action/exportarchive/surveyid/${survey.sid}`
          ),
          disabled: {
            state: !isSurveyActive,
            tooltip: t('Only available for active surveys'),
          },
        },
        {
          type: 'item',
          label: t('queXML format (.xml)'),
          url: getSiteUrl(
            `/admin/export/sa/survey/action/exportstructurequexml/surveyid/${survey.sid}`
          ),
        },
        {
          type: 'item',
          label: t('Tab-separated-values format (.txt)'),
          url: getSiteUrl(
            `/admin/export/sa/survey/action/exportstructuretsv/surveyid/${survey.sid}`
          ),
        },
        {
          type: 'item',
          label: t('Printable survey (.html)'),
          url: getSiteUrl(
            `/admin/export/sa/survey/action/exportprintables/surveyid/${survey.sid}`
          ),
        },
        {
          type: 'item',
          label: t('Printable survey'),
          url: getSiteUrl(
            `/admin/printablesurvey/sa/index/surveyid/${survey.sid}`
          ),
        },
      ],
    },
    {
      type: 'item',
      label: t('Import'),
      disabled: {
        state: true,
        tooltip: t('Coming soon'),
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
  ]

  const dropdownToggleSettings = {
    iconClassName: 'ri-more-fill',
    variant: 'light',
    id: 'topbarNavigation',
    title: '',
  }
  return (
    <div className="d-flex align-items-center">
      <span className="small text-muted me-1">
        {process.env.REACT_APP_DEV_MODE && '(Dev Mode) '}
        {process.env.REACT_APP_DEMO_MODE && '(Demo Mode) '}
      </span>
      <div id="auto-saved" className="d-flex align-items-center me-2">
        <p
          className={classNames(`m-0 me-2 auto-saved`, {
            'text-success': operationsBuffer.isEmpty(),
            'text-secondary': !operationsBuffer.isEmpty(),
          })}
        >
          {saveState}
        </p>
      </div>
      <Dropdown
        menuItems={dropdownMenuItems}
        toggleSettings={dropdownToggleSettings}
      />
      {showPreviewButton && (
        <a
          target="_blank"
          rel="noreferrer"
          href={survey.previewLink}
          className="preview-button me-2 p-0 d-flex align-items-center justify-content-center btn btn-light"
          id="preview-button"
        >
          <EyeIcon className="" />
        </a>
      )}
      {isSurveyActive && showShareButton && (
        <div
          onClick={() => setShowOverviewModalRef.current(true)}
          className="preview-button me-2 d-flex align-items-center justify-content-center btn btn-light"
        >
          <i className="ri-share-forward-line"></i>
        </div>
      )}
      <ActionButton
        className="me-2"
        survey={survey}
        operationsLength={operationsLength}
        triggerPublish={triggerPublish}
        showShareActionButton={showShareActionButton}
        showExportResponsesButton={showExportResponsesButton}
        showExportStatisticsButton={showExportStatisticsButton}
        showPublishSettings={showPublishSettings}
      />
      <PluginSlot slotName={PLUGIN_SLOTS.TOP_BAR_RIGHT} />
      <div>
        {isAddingQuestionOrGroup && (
          <TopBarQuestionInserter surveyID={survey.sid} />
        )}
      </div>
    </div>
  )
}
