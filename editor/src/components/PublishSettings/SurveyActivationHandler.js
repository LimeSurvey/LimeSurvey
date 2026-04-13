import {
  forwardRef,
  useEffect,
  useImperativeHandle,
  useMemo,
  useRef,
  useState,
} from 'react'
import { useNavigate, useParams } from 'react-router-dom'

import {
  createBufferOperation,
  Entities,
  errorToast,
  GetInvalidSurveyObjects,
  htmlPopup,
  Operations,
  STATES,
  Toast,
  isSurveyExpired,
  SURVEY_MENU_TITLES,
} from 'helpers'
import {
  useAppState,
  useBuffer,
  useFocused,
  useSurvey,
  useOperationCallback,
  useSurveyArchive,
} from 'hooks'
import { ComponentModal } from 'components/Modals'
import { getSharingPanels } from 'shared/getSharingPanels'
import {
  getPublicationPath,
  getSurveyDeactivationPopupOptions,
  getSurveyDeactivationToastOptions,
  getSurveyPauseToastOptions,
  getSurveyReactivationPopupOptions,
  SURVEY_STATUS_SWITCH_TYPES,
  SurveyOverview,
} from 'components'

const SurveyActivationHandler = forwardRef(
  ({ showOverViewModal, setShowOverViewModal }, ref) => {
    const { surveyId: surveyIdParam } = useParams()
    const { survey = {}, update } = useSurvey(surveyIdParam)
    const { surveyArchives, fetchSurveyArchives } =
      useSurveyArchive(surveyIdParam)
    const { setFocused } = useFocused()
    const { operationsBuffer, addToBuffer } = useBuffer()
    const navigate = useNavigate()
    const { subscribeToOperationFinish } = useOperationCallback()
    const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
    const [numberOfQuestions] = useAppState(STATES.NUMBER_OF_QUESTIONS)
    const [isSurveyActiveAfterPublish, setIsSurveyActiveAfterPublish] =
      useState(false)

    const [hasSurveyUpdatePermission] = useAppState(
      STATES.HAS_SURVEY_UPDATE_PERMISSION
    )
    const [isSurveyActive, setIsSurveyActive] = useAppState(
      STATES.IS_SURVEY_ACTIVE,
      false
    )

    const [, setSurveyPublishRunning] = useAppState(
      STATES.SURVEY_PUBLISH_RUNNING,
      false
    )
    const selectedArchiveTableRef = useRef(null)

    const surveyHasArchivedResponses = useMemo(
      () => surveyArchives.length > 0,
      [surveyArchives]
    )

    const responseTableSelectionOptions = useMemo(() => {
      return {
        options: surveyArchives || [],
        onChange: (table) => {
          selectedArchiveTableRef.current = table
        },
      }
    }, [surveyArchives])

    const runStatusSwitchOperation = (switchType) => {
      const operationProps = { anonymized: false }
      let shouldCallImport = false

      switch (switchType) {
        case SURVEY_STATUS_SWITCH_TYPES.DEACTIVATE:
          operationProps.deactivate = true
          break
        case SURVEY_STATUS_SWITCH_TYPES.PAUSE:
          operationProps.expire = true
          break
        case SURVEY_STATUS_SWITCH_TYPES.START_FROM_SCRATCH:
          operationProps.activate = true
          break
        case SURVEY_STATUS_SWITCH_TYPES.KEEP_RESPONSES:
          operationProps.activate = true
          shouldCallImport = true
          break
      }

      const surveyStatusOperation = createBufferOperation(survey.sid)
        .surveyStatus()
        .update(operationProps)

      addToBuffer(surveyStatusOperation)

      if (shouldCallImport) {
        const archiveTimestamp = selectedArchiveTableRef.current
          ? selectedArchiveTableRef.current.timestamp
          : surveyArchives[0].timestamp
        const importResponsesOperation = createBufferOperation(survey.sid)
          .importResponses()
          .update({
            preserveIDs: false,
            timestamp: +archiveTimestamp,
          })

        addToBuffer(importResponsesOperation)
      }
    }

    const isSurveyInvalid = () => {
      const invalidSurveyObjects = GetInvalidSurveyObjects(survey)

      const groupKeys = Object.keys(
        invalidSurveyObjects?.gid ? invalidSurveyObjects?.gid : {}
      )
      const questionKeys = Object.keys(
        invalidSurveyObjects?.qid ? invalidSurveyObjects?.qid : {}
      )

      if (groupKeys.length) {
        const firstGroup = invalidSurveyObjects.gid[groupKeys[0]]
        setFocused(
          {
            ...survey.questionGroups[firstGroup.groupIndex],
          },
          firstGroup.groupIndex
        )
      }

      if (questionKeys.length) {
        const firstQuestion = invalidSurveyObjects.qid[questionKeys[0]]
        setFocused(
          {
            ...survey.questionGroups[firstQuestion.groupIndex].questions[
              firstQuestion.questionIndex
            ],
          },
          firstQuestion.groupIndex,
          firstQuestion.questionIndex
        )
      }

      return questionKeys.length || groupKeys.length
    }

    const handleStatusSwitchAction = (switchType, shouldActivate) => {
      const isPauseSwitch = switchType === SURVEY_STATUS_SWITCH_TYPES.PAUSE
      const isSurveyActiveAfterOperation = shouldActivate || isPauseSwitch

      setSurveyPublishRunning(true)
      subscribeToOperationFinish({
        entity: Entities.surveyStatus,
        operation: Operations.update,
        callback: (extraData) =>
          onSwitchComplete(isSurveyActiveAfterOperation, switchType, extraData),
      })
      runStatusSwitchOperation(switchType)
    }

    const togglePublish = () => {
      const shouldActivate = !isSurveyActive
      const surveyIsExpired = isSurveyExpired(survey.expires)
      setShowOverViewModal(false)

      if (isSurveyInvalid()) {
        errorToast(
          'Invalid survey questions. Please resolve the errors and try again.'
        )
        return
      }

      if (shouldActivate && !surveyHasArchivedResponses) {
        setIsSurveyActiveAfterPublish(true)
        handleStatusSwitchAction(
          SURVEY_STATUS_SWITCH_TYPES.START_FROM_SCRATCH,
          shouldActivate
        )
        return
      }

      const popupProps = {
        surveyId: survey.sid,
        responseTableSelectionOptions,
        surveyIsExpired,
        onConfirm: (switchType) =>
          handleStatusSwitchAction(switchType, shouldActivate),
        navigateToPublication: () => navigate(getPublicationPath(survey.sid)),
      }

      const popupOptions = shouldActivate
        ? getSurveyReactivationPopupOptions(popupProps)
        : getSurveyDeactivationPopupOptions(popupProps)

      selectedArchiveTableRef.current = null // reset selected archive table reference
      htmlPopup(popupOptions)
    }

    const onSwitchComplete = async (
      isSurveyActiveAfterOperation,
      switchType,
      extraData
    ) => {
      setSurveyPublishRunning(false)
      setIsSurveyActive(isSurveyActiveAfterOperation)

      const surveyUpdateFields = { active: isSurveyActiveAfterOperation }
      if (extraData?.expire) {
        surveyUpdateFields.expires = extraData.expire
      }
      update(surveyUpdateFields)

      const isLastSwitchAPauseOperation =
        switchType === SURVEY_STATUS_SWITCH_TYPES.PAUSE

      if (isLastSwitchAPauseOperation) {
        const PauseToastOptions = getSurveyPauseToastOptions(
          survey.sid,
          navigate
        )
        Toast(PauseToastOptions)
        return
      }

      if (isSurveyActiveAfterOperation) {
        setShowOverViewModal(true)
      } else {
        const deactivationToastOptions = getSurveyDeactivationToastOptions()
        Toast(deactivationToastOptions)
      }
    }

    useEffect(() => {
      fetchSurveyArchives(survey.sid)
    }, [survey.sid, isSurveyActive])

    useImperativeHandle(ref, () => ({
      togglePublish,
    }))

    return (
      <>
        <ComponentModal
          show={showOverViewModal}
          onHide={() => {
            setShowOverViewModal(false)
            setIsSurveyActiveAfterPublish(false)
          }}
          modalClassname="w-100"
          headerClassname="position-absolute end-0"
          Component={
            <SurveyOverview
              survey={survey}
              update={update}
              hasOperations={!operationsBuffer.isEmpty()}
              hasSurveyUpdatePermission={hasSurveyUpdatePermission}
              numberOfQuestions={numberOfQuestions}
              setShowSharingPanel={() => {
                setShowOverViewModal(false)
                navigate(
                  `/survey/${surveyIdParam}/${getSharingPanels().sharing.panel}/${SURVEY_MENU_TITLES.sharingOverview}`
                )
              }}
              setShowOverViewModal={setShowOverViewModal}
              editSurvey={() => {
                setShowOverViewModal(false)
                navigate(`/survey/${surveyIdParam}`)
              }}
              togglePublish={togglePublish}
              toastMessage={
                isSurveyActiveAfterPublish &&
                isSurveyActive &&
                '🎉 ' + t('Congrats! Your survey has been activated.')
              }
              activeLanguage={activeLanguage}
              createBufferOperation={createBufferOperation}
              addToBuffer={addToBuffer}
            />
          }
        />
      </>
    )
  }
)

SurveyActivationHandler.displayName = 'SurveyActivationHandler'

export default SurveyActivationHandler
