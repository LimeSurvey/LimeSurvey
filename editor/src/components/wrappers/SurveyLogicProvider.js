import React, { useEffect } from 'react'
import { useParams } from 'react-router-dom'
import { format } from 'util'
import { queryClient } from 'queryClient'

import {
  useAppState,
  useBuffer,
  useErrors,
  useFocused,
  useInFlightConditionQids,
  useSurvey,
  useSurveyRequestTimestamp,
} from 'hooks'
import {
  Entities,
  EntitiesType,
  Operations,
  OperationsBuffer,
  STATES,
  errorToast,
  applyQuestionRelevance,
  applySkippedQuestionRelevance,
  clearSkippedQuestionRelevance,
  forgetSkippedQuestionRelevance,
  clearResolvedTempIds,
  getConditionLogicQids,
  hasCopiedConditions,
  processValidationErrorsAndUpdateOperations,
  rememberResolvedTempIds,
  removeConditionsWithUnknownTempIdsFromSurveyAndBuffer,
  replaceTempIdsInSurveyAndBuffer,
  dayJsHelper,
  RandomNumber,
} from 'helpers'
import {
  hasUnansweredConditionCreate,
  removeOperationsOfFailedCreates,
} from 'helpers/Buffer/operationsHandlers'

/**
 * Render children while loading the route's survey and saving buffered edits.
 * Reconcile patch results with local survey state, surface errors, and invalidate
 * response queries after receiving a patch result.
 */
export const SurveyLogicProvider = ({ children }) => {
  const { surveyId } = useParams()
  const { setFocused, groupIndex, questionIndex } = useFocused()

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
  const [, setInFlightConditionQids] = useInFlightConditionQids()
  const { clearSurveyRequestTimestamp } = useSurveyRequestTimestamp()

  /**
   * Clean the condition operations that could never be sent, before each save
   * (only when no save is running, so no create can be on its way):
   * - the conditions of, or using as source, an unsaved question, subquestion
   *   or group that was deleted: Its temp ID will never be replaced. They are
   *   removed from the survey (shown in the editor) and from the buffer, and
   *   this is their only cleanup;
   * - the operations still on a temp cid without a buffered create (see
   *   removeConditionOpsOfFailedCreates): For example the delete of a
   *   condition whose create was refused, which would otherwise block every
   *   save;
   * - the deletes of new questions and groups whose create failed (see
   *   removeDeletesOfFailedCreates).
   * It also applies the relevance of the server that were skipped for newer
   * condition changes canceled since (see applySkippedQuestionRelevance).
   * @returns {boolean} whether the buffer changed
   */
  const cleanConditionOperations = () => {
    // The buffer as it is now, not the one of this render: An operation added
    // since (e.g. by an effect of the same commit) must not be lost when the
    // cleaned buffer is written back.
    const operations = queryClient.getQueryData([STATES.BUFFER]) ?? []
    const result = removeConditionsWithUnknownTempIdsFromSurveyAndBuffer(
      queryClient.getQueryData([STATES.SURVEY])?.survey,
      operations
    )

    // A relevance of the server skipped for newer condition operations that
    // were canceled since, instead of being sent.
    const appliedRelevanceQids = applySkippedQuestionRelevance(
      result.survey,
      result.operations
    )

    if (result.changedQids.length || appliedRelevanceQids.length) {
      update({ ...result.survey })
    }

    const cleanedOperations = removeOperationsOfFailedCreates(result.operations)
    if (cleanedOperations === operations) return false

    setBuffer(cleanedOperations, RandomNumber())
    return true
  }

  // Mount / ID Change Logic
  useEffect(() => {
    clearBuffer()
    clearErrors()
    setHelpersSettings({})
    setIsPatchSurveyRunning(false)
    setIsAddingQuestionOrGroup(false)
    setInFlightConditionQids([])
    clearResolvedTempIds()
    clearSkippedQuestionRelevance()

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

    // The buffer changed: This effect runs again with the cleaned buffer.
    if (cleanConditionOperations()) return

    const readyOperations = operationsBuffer.getOperations({ ready: true })
    // surveyRefreshRequired is only updated on the next render, too late for finallyCallback
    let refreshRequired = false

    const beforeCallback = () => {
      // Clear ready buffer items
      // - items that are not ready may be waiting for tempId to be resolved
      operationsBuffer.clearBuffer({ ready: true })
      clearBuffer({ ready: true })
      setInFlightConditionQids(getConditionLogicQids(readyOperations))
      forgetSkippedQuestionRelevance(readyOperations)
      setIsPatchSurveyRunning(true)
    }

    // currentBuffer
    /**
     * Apply a patch result's ID mappings and errors to the survey and buffer,
     * update save status, and invalidate responses even if no operations applied.
     */
    const thenCallback = (result) => {
      const survey = queryClient.getQueryData([STATES.SURVEY]).survey
      const focused = queryClient.getQueryData([STATES.FOCUSED_ENTITY]).focused
      const currentBuffer = new OperationsBuffer(
        queryClient.getQueryData([STATES.BUFFER]),
        RandomNumber()
      )

      const questionsTempIdMapping = result.tempIdMapping?.questionsMap
      const subquestionsTempIdMapping = result.tempIdMapping?.subquestionsMap
      const groupsTempIdMapping = result.tempIdMapping?.questionGroupsMap
      const answersTempIdMapping = result.tempIdMapping?.answersMap
      const conditionsTempIdMapping = result.tempIdMapping?.conditionsMap

      ;[
        groupsTempIdMapping,
        questionsTempIdMapping,
        subquestionsTempIdMapping,
        answersTempIdMapping,
        conditionsTempIdMapping,
      ].forEach(rememberResolvedTempIds)

      // Groups, questions, subquestions and conditions: One pass over the survey
      // and the buffer for all of them (see replaceTempIdsInSurveyAndBuffer).
      // Answers after them, in the answers only.
      let mapResult = replaceTempIdsInSurveyAndBuffer(
        [
          ...(groupsTempIdMapping ?? []),
          ...(questionsTempIdMapping ?? []),
          ...(subquestionsTempIdMapping ?? []),
          ...(conditionsTempIdMapping ?? []),
        ],
        EntitiesType.question,
        survey,
        currentBuffer,
        focused
      )

      if (
        mapResult.focused &&
        (groupsTempIdMapping?.length || questionsTempIdMapping?.length)
      ) {
        setFocused(mapResult.focused, groupIndex, questionIndex)
      }

      mapResult = replaceTempIdsInSurveyAndBuffer(
        answersTempIdMapping,
        EntitiesType.answer,
        mapResult.survey,
        mapResult.operationsBuffer
      )

      mapResult = applyQuestionRelevance(
        mapResult,
        result.extras?.questionRelevance
      )

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

      // A refused delete already removed the item locally: Drop the operation
      // and reload the survey to show the item again.
      const refusedDeletes = exceptionErrors.filter(
        (error) => error.op === Operations.delete
      )
      refusedDeletes.forEach((error) => {
        mapResult.operationsBuffer.removeOperation(
          error.id,
          error.op,
          error.entity
        )
      })
      // A refused condition change is not sent again: The editor shows it,
      // reload the survey to show the conditions the server has.
      const hasRefusedConditionChange = [
        ...validationErrors,
        ...exceptionErrors,
      ].some((error) => error.entity === Entities.questionCondition)
      // A condition create left without a real cid: The request failed, or
      // the server skipped the condition. The editor shows a condition the
      // server doesn't have, and its later changes can't be sent.
      const hasLostConditionCreate = hasUnansweredConditionCreate(
        readyOperations,
        conditionsTempIdMapping
      )
      // A duplicated question was created with copies of the original's
      // conditions, whose new IDs are only known from the survey.
      const hasCreatedCopiedConditions = readyOperations.some(
        (operation) =>
          operation.entity === Entities.question &&
          operation.op === Operations.create &&
          hasCopiedConditions(operation.props?.question)
      )
      if (
        refusedDeletes.length ||
        hasRefusedConditionChange ||
        hasLostConditionCreate ||
        hasCreatedCopiedConditions
      ) {
        // Reload the whole survey, even if the server reports it as unchanged
        // since the last request.
        clearSurveyRequestTimestamp(surveyId)
        refreshRequired = true
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

      // The response brings the field names when it created questions or
      // subquestions: Those of the new ones are known together with their
      // IDs. Otherwise (e.g. after a type change) they are fetched again.
      const questionFieldnames = result.extras?.questionFieldnames
      if (questionFieldnames) {
        queryClient.setQueryData(
          [STATES.SURVEY_QUESTIONS_FIELDNAME],
          questionFieldnames
        )
      }
      update({ ...mapResult.survey })
      if (!questionFieldnames) refetchQuestionsFieldNamesMap()
      // An operation still on a temp ID without a buffered create now belongs
      // to a creation that failed or was refused: A condition change, or the
      // delete of a new question or group.
      // Before the reload check of finallyCallback, which needs an empty buffer.
      mapResult.operationsBuffer.setOperations(
        removeOperationsOfFailedCreates(mapResult.operationsBuffer.operations)
      )

      setBuffer(mapResult.operationsBuffer.getOperations(), RandomNumber())

      setSaveState(
        format(
          t('Saved at %s'),
          dayJsHelper(new Date().getTime()).format('hh:mm')
        )
      )
      setErrorsFromPatchResponse([...validationErrors, ...exceptionErrors])

      // Refresh responses after survey metadata changes.
      queryClient.invalidateQueries({
        queryKey: [STATES.SURVEY_RESPONSES, surveyId],
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
      // Condition operations are not put back, as when the API refuses them
      // (see processValidationErrorsAndUpdateOperations): The survey is
      // reloaded after a later save, to show the conditions the server has.
      // The operations left waiting for the real ID of a condition create are
      // dropped before the next save (cleanConditionOperations).
      const updatedOperation = readyOperations
        .filter((operation) => operation.entity !== Entities.questionCondition)
        .map((operation) => ({
          ...operation,
          error: true,
        }))

      updatedOperation.forEach((operation) => {
        addToBuffer(operation, false)
      })

      if (updatedOperation.length !== readyOperations.length) {
        setSurveyRefreshRequired(true)
      }

      setErrorsFromPatchResponse([...updatedOperation])
      setSaveState(
        `Error happened ${dayJsHelper(new Date().getTime()).format('hh:mm')}`
      )

      errorToast(error.message)
    }

    const finallyCallback = () => {
      setIsPatchSurveyRunning(false)
      setInFlightConditionQids([])

      // maybe there's some delayed operations.
      const pendingOperations = new OperationsBuffer(
        queryClient.getQueryData([STATES.BUFFER])
      )
      if (
        pendingOperations.isEmpty() &&
        (surveyRefreshRequired || refreshRequired)
      ) {
        fetchSurvey(surveyId)
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
