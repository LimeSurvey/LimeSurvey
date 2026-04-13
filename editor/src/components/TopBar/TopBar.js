import { useEffect, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'

import { useAppState, useBuffer, useErrors, useSurvey } from 'hooks'
import {
  RemoveHTMLTagsInString,
  STATES,
  createBufferOperation,
  URLS,
  isSurveyExpired,
} from 'helpers'
import { AddQuestion } from 'components/Survey/AddQuestion'
import SurveyActivationHandler from 'components/PublishSettings/SurveyActivationHandler'

import LogoIcon from '../../assets/images/logo-icon-black.png'
//import { ButtonBackToClassicEditor } from './Button/ButtonBackToClassicEditor'
import { SurveyTitleSelector } from './SurveyTitleSelector'
import { TopBarActions } from './TopBarActions'

export const TopBar = ({
  surveyId,
  surveyActivationHandlerRef,
  showAddQuestionButton = true,
  showPublishSettings = true,
  showPreviewButton = true,
  showShareButton = true,
  showShareActionButton = false,
  showExportResponsesButton = false,
  showExportStatisticsButton = false,
  setShowOverviewModalRef,
}) => {
  const { survey, update, surveyList } = useSurvey(surveyId)
  const { getError } = useErrors()
  const navigate = useNavigate()
  const { operationsBuffer, addToBuffer } = useBuffer()
  const [, setFocusedQuestionGroup] = useState({})
  const [saveState] = useAppState(STATES.SAVE_STATE)
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)
  const [currentActiveLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
  const [showOverViewModal, setShowOverViewModal] = useState(false)

  const activeLanguage = useMemo(
    () =>
      currentActiveLanguage && currentActiveLanguage.length > 0
        ? currentActiveLanguage
        : survey.language,
    [currentActiveLanguage, survey.language]
  )

  const [isAddingQuestionOrGroup] = useAppState(
    STATES.IS_ADDING_QUESTION_OR_GROUP,
    false
  )

  const operationsLength = useMemo(() => {
    return operationsBuffer?.getOperations()?.length
  }, [operationsBuffer.getOperations()?.length])

  const onSurveyTitleChange = (title) => {
    let updatedTitle = RemoveHTMLTagsInString(title).replaceAll('&nbsp;', '')
    updatedTitle = (updatedTitle.trim() === '') === '' ? '' : updatedTitle

    const operation = createBufferOperation(survey.sid)
      .languageSetting()
      .update({
        [activeLanguage]: {
          title: updatedTitle,
        },
      })

    addToBuffer(operation)
    update({
      languageSettings: {
        ...survey.languageSettings,
        [activeLanguage]: {
          ...survey.languageSettings[activeLanguage],
          title: updatedTitle,
        },
      },
    })
  }

  const handleSurveySwitch = async (e) => {
    //what is going on here?
    navigate(`/survey/${e.target.value}`)
  }

  const triggerPublish = () => {
    if (surveyActivationHandlerRef.current) {
      surveyActivationHandlerRef.current.togglePublish({
        shouldActivate: !isSurveyActive,
        surveyIsExpired: isSurveyExpired(survey.expires),
      })
    }
  }

  useEffect(() => {
    setFocusedQuestionGroup(null)
    if (setShowOverviewModalRef?.current !== undefined) {
      setShowOverviewModalRef.current = setShowOverViewModal
    }
  }, [survey.sid])

  return (
    <div id="topbar" className={`top-bar d-flex w-100 justify-content-between`}>
      <div className="top-bar-left d-flex ps-2 pe-3 ms-1 justify-content-between">
        <a className="top-bar-brand" data-testid="logo-a-tag" href={URLS.ADMIN}>
          <img className="logo" src={LogoIcon} height={34} alt={t('Logo')} />{' '}
          LimeSurvey
        </a>
        {showAddQuestionButton && (
          <AddQuestion id={'topbar-add-question'} className={'ms-auto'} />
        )}
      </div>
      <div className="top-bar-middle d-flex flex-grow-1 justify-content-center align-items-center">
        <SurveyTitleSelector
          surveyId={surveyId}
          survey={survey}
          surveyList={surveyList}
          activeLanguage={activeLanguage}
          onSurveyTitleChange={onSurveyTitleChange}
          handleSurveySwitch={handleSurveySwitch}
          getError={getError}
        />
      </div>
      <TopBarActions
        surveyId={surveyId}
        showPreviewButton={showPreviewButton}
        showShareButton={showShareButton}
        isSurveyActive={isSurveyActive}
        survey={survey}
        operationsLength={operationsLength}
        operationsBuffer={operationsBuffer}
        saveState={saveState}
        showShareActionButton={showShareActionButton}
        showExportResponsesButton={showExportResponsesButton}
        showExportStatisticsButton={showExportStatisticsButton}
        showPublishSettings={showPublishSettings}
        triggerPublish={triggerPublish}
        isAddingQuestionOrGroup={isAddingQuestionOrGroup}
        setShowOverviewModalRef={setShowOverviewModalRef}
      />
      <SurveyActivationHandler
        ref={surveyActivationHandlerRef}
        setShowOverViewModal={setShowOverViewModal}
        showOverViewModal={showOverViewModal}
      />
    </div>
  )
}
