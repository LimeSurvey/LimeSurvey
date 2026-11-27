import React, { useEffect } from 'react'
import { useParams } from 'react-router-dom'
import { format } from 'util'
import { queryClient } from 'queryClient'

import { useAppState, useBuffer, useErrors, useFocused, useSurvey } from 'hooks'
import {
  Entities,
  EntitiesType,
  Operations,
  OperationsBuffer,
  STATES,
  errorToast,
  getEntityInfo,
  processValidationErrorsAndUpdateOperations,
  replaceTempIdsInSurveyAndBuffer,
  updateSurveyEntitiesData,
  dayJsHelper,
  RandomNumber,
} from 'helpers'

// Error code of a valid change the current state of the survey doesn't allow (PersistErrorException::CODE_CHANGE_REFUSED)
const CHANGE_REFUSED_CODE = 409

/**
 * Drops a refused change from the buffer. Only switching "Other" off is refused
 * with CHANGE_REFUSED_CODE: The other changes merged into the same operation
 * (e.g. a new code) stay in the buffer and are saved again. Without "Other" in the
 * operation, the whole operation is dropped, so a refusal is never sent again.
 *
 * @param {OperationsBuffer} operationsBuffer The buffer to update
 * @param {object} error The exception error of the refused operation
 */
const dropRefusedChange = (operationsBuffer, error) => {
  const operation = operationsBuffer.getOperation(
    error.id,
    error.op,
    error.entity
  )
  if (
    Number(error.code) === CHANGE_REFUSED_CODE &&
    'other' in (operation?.props ?? {})
  ) {
    delete operation.props.other
    if (Object.keys(operation.props).length) {
      delete operation.error
      return
    }
  }
  operationsBuffer.removeOperation(error.id, error.op, error.entity)
}

/**
 * Render children while loading the route's survey and saving buffered edits.
 * Reconcile patch results with local survey state, surface errors, and invalidate
 * response queries after receiving a patch result.
 */
export const SurveyLogicProvider = ({ children }) => {
  const { surveyId } = useParams()
  const { setFocused } = useFocused()

  const { surveyPatch, fetchSurvey, update, refetchQuestionsFieldNamesMap } =
    useSurvey(surveyId)
  const { operationsBuffer, addToBuffer, setBuffer, clearBuffer } = useBuffer()
  const { setErrorsFromPatchResponse, clearErrors } = useErrors()

  const [surveyRefreshRequired, setSurveyRefreshRequired] = useAppState(
    STATES.SURVEY_REFRESH_REQUIRED
  )
  const [isPatchSurveyRunning, setIsPatchSurveyRunning] = useAppState(
    STATES.IS_PATCH_SURVEY_RUNNING,
    false
  )
  const [, setSaveState] = useAppState(STATES.SAVE_STATE, '')
  const [, setHelpersSettings] = useAppState(STATES.HELPER_SETTINGS, {})
  const [, setIsAddingQuestionOrGroup] = useAppState(
    STATES.IS_ADDING_QUESTION_OR_GROUP,
    false
  )

  // Mount / ID Change Logic
  useEffect(() => {
    clearBuffer()
    clearErrors()
    setHelpersSettings({})
    setIsPatchSurveyRunning(false)
    setIsAddingQuestionOrGroup(false)

    if (surveyId) {
      fetchSurvey(surveyId)
      refetchQuestionsFieldNamesMap()
    }
  }, [surveyId])

  // Window Unload Safety
  useEffect(() => {
    const handleBeforeUnload = (event) => {
      if (operationsBuffer.getOperations()?.length > 0) {
        event.preventDefault()
      }
    }
    window.addEventListener('beforeunload', handleBeforeUnload)
    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload)
    }
  }, [operationsBuffer])

  // Auto-Save / Patch Loop
  useEffect(() => {
    if (process.env.REACT_APP_DEMO_MODE === 'true' || isPatchSurveyRunning) {
      return
    }

    const readyOperations = operationsBuffer.getOperations({ ready: true })
    // surveyRefreshRequired is only updated on the next render, too late for finallyCallback
    let changeRefused = false

    const beforeCallback = () => {
      // Clear ready buffer items
      // - items that are not ready may be waiting for tempId to be resolved
      operationsBuffer.clearBuffer({ ready: true })
      clearBuffer({ ready: true })
      setIsPatchSurveyRunning(true)
    }

    // currentBuffer
    /**
     * Apply a patch result's ID mappings and errors to the survey and buffer,
     * update save status, and invalidate responses even if no operations applied.
     */
    const thenCallback = (result) => {
      const survey = queryClient.getQueryData([STATES.SURVEY]).survey
      const focusState = queryClient.getQueryData([STATES.FOCUSED_ENTITY]) ?? {}
      const focused = focusState.focused
      const currentBuffer = new OperationsBuffer(
        queryClient.getQueryData([STATES.BUFFER]),
        RandomNumber()
      )

      const questionsTempIdMapping = result.tempIdMapping?.questionsMap
      const subquestionsTempIdMapping = result.tempIdMapping?.subquestionsMap
      const groupsTempIdMapping = result.tempIdMapping?.questionGroupsMap
      const answersTempIdMapping = result.tempIdMapping?.answersMap
      const conditionsTempIdMapping = result.tempIdMapping?.conditionsMap

      let mapResult = replaceTempIdsInSurveyAndBuffer(
        groupsTempIdMapping,
        EntitiesType.group,
        survey,
        currentBuffer,
        focused
      )

      if (mapResult.focused && groupsTempIdMapping) {
        setFocused(
          mapResult.focused,
          focusState.groupIndex,
          focusState.questionIndex
        )
      }

      mapResult = replaceTempIdsInSurveyAndBuffer(
        questionsTempIdMapping,
        EntitiesType.question,
        mapResult.survey,
        mapResult.operationsBuffer,
        focused
      )

      if (mapResult.focused && questionsTempIdMapping) {
        setFocused(
          mapResult.focused,
          focusState.groupIndex,
          focusState.questionIndex
        )
      }

      mapResult = replaceTempIdsInSurveyAndBuffer(
        subquestionsTempIdMapping,
        EntitiesType.subquestion,
        mapResult.survey,
        mapResult.operationsBuffer
      )

      mapResult = replaceTempIdsInSurveyAndBuffer(
        answersTempIdMapping,
        EntitiesType.answer,
        mapResult.survey,
        mapResult.operationsBuffer
      )

      mapResult = replaceTempIdsInSurveyAndBuffer(
        conditionsTempIdMapping,
        EntitiesType.condition,
        mapResult.survey,
        mapResult.operationsBuffer
      )

      if (result.extras) {
        mapResult = updateSurveyEntitiesData(
          EntitiesType.question,
          mapResult.survey,
          mapResult.operationsBuffer,
          result.extras
        )
      }

      // The operations that were sent to the API.
      const readyOperationsBuffer = new OperationsBuffer(readyOperations)

      let validationErrors = processValidationErrorsAndUpdateOperations(
        result.validationErrors || [],
        readyOperationsBuffer,
        mapResult.operationsBuffer
      )

      let exceptionErrors = processValidationErrorsAndUpdateOperations(
        result.exceptionErrors || [],
        readyOperationsBuffer,
        mapResult.operationsBuffer
      )

      // A refused deletion already removed the item locally, and a change the survey
      // doesn't allow (switching "Other" off while conditions use it) is already shown:
      // Drop the refused change (see dropRefusedChange()) and reload the survey to show the saved state again.
      // Other failed updates stay in the buffer marked as error, so the user can correct them.
      const refusedChanges = exceptionErrors.filter(
        (error) =>
          error.op === Operations.delete ||
          Number(error.code) === CHANGE_REFUSED_CODE
      )
      refusedChanges.forEach((error) =>
        dropRefusedChange(mapResult.operationsBuffer, error)
      )
      if (refusedChanges.length) {
        changeRefused = true
        setSurveyRefreshRequired(true)
      }

      // A refused survey status change (e.g. activation blocked by a plugin) is
      // not retried automatically: Drop it, so the user can trigger it again.
      exceptionErrors
        .filter((error) => error.entity === Entities.surveyStatus)
        .forEach((error) => {
          mapResult.operationsBuffer.removeOperation(
            error.id,
            error.op,
            error.entity
          )
        })

      update({ ...mapResult.survey })
      refetchQuestionsFieldNamesMap()
      setBuffer(mapResult.operationsBuffer.getOperations(), RandomNumber())

      setSaveState(
        format(
          t('Saved at %s'),
          dayJsHelper(new Date().getTime()).format('HH:mm')
        )
      )
      setErrorsFromPatchResponse([...validationErrors, ...exceptionErrors])

      // Refresh responses and statistics after survey metadata changes.
      queryClient.invalidateQueries({
        queryKey: [STATES.SURVEY_RESPONSES, surveyId],
      })
      queryClient.invalidateQueries({
        queryKey: [STATES.SURVEY_STATISTICS, surveyId],
      })

      // Specific errors are already shown as toasts, see SurveyWorkspace
      const hasErrorMessages =
        result.validationErrors?.length || result.exceptionErrors?.length
      if (!result.operationsApplied && !hasErrorMessages) {
        errorToast(
          t(
            'Sorry, we encountered an issue while saving the changes. Please try refreshing the page!'
          )
        )
      }
    }

    const catchCallback = (error) => {
      const updatedOperation = readyOperations.map((operation) => ({
        ...operation,
        error: true,
      }))

      updatedOperation.forEach((operation) => {
        addToBuffer(operation, false)
      })

      setErrorsFromPatchResponse([...updatedOperation])
      setSaveState(
        `Error happened ${dayJsHelper(new Date().getTime()).format('hh:mm')}`
      )

      errorToast(error.message)
    }

    const finallyCallback = () => {
      setIsPatchSurveyRunning(false)

      // maybe there's some delayed operations.
      const pendingOperations = new OperationsBuffer(
        queryClient.getQueryData([STATES.BUFFER])
      )
      if (
        pendingOperations.isEmpty() &&
        (surveyRefreshRequired || changeRefused)
      ) {
        // The question settings show a copy of the focused question: Refresh it too,
        // also when a refusal waited for later edits. Nothing is pending, so the
        // reloaded question is the current one.
        fetchSurvey(surveyId)
          .then(({ survey } = {}) => {
            // Another survey was opened meanwhile: Its focus must stay
            const requestedSurveyId = queryClient.getQueryData([
              STATES.REQUESTED_SURVEY_ID,
            ])
            if (
              String(requestedSurveyId) !== String(surveyId) ||
              String(survey?.sid) !== String(surveyId)
            ) {
              return
            }
            // Edits made during the reload are shown already and not saved yet:
            // Restore the focus with the next reload, after they are saved
            const hasPendingEdits =
              !new OperationsBuffer(
                queryClient.getQueryData([STATES.BUFFER])
              ).isEmpty() ||
              queryClient.getQueryData([
                'appState',
                STATES.IS_PATCH_SURVEY_RUNNING,
              ])
            if (hasPendingEdits) {
              setSurveyRefreshRequired(true)
              return
            }
            const { focused } =
              queryClient.getQueryData([STATES.FOCUSED_ENTITY]) || {}
            if (!focused?.qid || !survey?.questionGroups) {
              return
            }
            // By ID: A restored deletion can move the question to another index
            const {
              question,
              groupIndex: currentGroupIndex,
              questionIndex: currentQuestionIndex,
            } = getEntityInfo(focused.qid, survey, EntitiesType.question)
            if (question) {
              setFocused(
                question,
                currentGroupIndex,
                currentQuestionIndex,
                false
              )
            }
          })
          .catch((error) => errorToast(error.message))
        setSurveyRefreshRequired(false)
      }
    }

    surveyPatch(
      readyOperations,
      beforeCallback,
      thenCallback,
      finallyCallback,
      catchCallback
    )
  }, [operationsBuffer.bufferHash, isPatchSurveyRunning])

  return <>{children}</>
}
