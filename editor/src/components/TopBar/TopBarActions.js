import { useMemo } from 'react'
import classNames from 'classnames'
import { useAppState } from 'hooks'
import { STATES } from 'helpers'
import { Dropdown } from 'components/UIComponents/Dropdown/Dropdown'
import { EyeIcon } from 'components/icons'
import { ActionButton } from './Button/ActionButton'
import { TopBarQuestionInserter } from './TopBarQuestionInserter'
import { getTopBarDropdownItems } from './getTopBarDropdownItems'
import { PLUGIN_SLOTS } from 'plugins/slots'
import { PluginSlot } from 'plugins/PluginSlot'

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
  const [, startEditorTutorial] = useAppState(
    STATES.START_EDITOR_TUTORIAL,
    false
  )

  const handleStartEditorTutorial = () => {
    startEditorTutorial(true)
  }

  const dropdownMenuItems = useMemo(
    () =>
      getTopBarDropdownItems({
        surveyId,
        surveySid: survey.sid,
        isSurveyActive,
        handleStartEditorTutorial,
      }),
    [surveyId, survey.sid, isSurveyActive, handleStartEditorTutorial]
  )

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
      {/*<ButtonBackToClassicEditor className="me-2" surveyId={surveyId} />*/}

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
      <Dropdown
        menuItems={dropdownMenuItems}
        toggleSettings={dropdownToggleSettings}
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
