import { useEffect, useMemo, useState, useRef } from 'react'
import classNames from 'classnames'

import { singleChoiceThemes } from '../../../QuestionTypes'
import { questionPreviewComponents } from '../../../QuestionTypes/questionPreviewComponents'
import { Button } from 'components/UIComponents'
import { Toast } from 'helpers'

export const QuestionBodyPreview = ({
  question,
  language,
  isHovered,
  isQuestionDisabled,
  surveySettings,
  valueInfo = {},
  onSave = () => {},
  onCancel = () => {},
  disableUpdatingResponses,
}) => {
  const [children, setChildren] = useState([])
  // The changed response values, the fields whose input can't be saved and whether there are
  // unconfirmed changes, kept together so every change can be applied as one functional update
  const emptyPending = { value: {}, invalidKeys: [], isUpdated: false }
  const [pending, setPending] = useState(emptyPending)
  const { value, invalidKeys, isUpdated: valueIsUpdated } = pending
  // An invalid input keeps Cancel available, so it can be reset, but can't be confirmed
  const showButtons = valueIsUpdated || invalidKeys.length > 0
  const lastToastTimeRef = useRef(0)

  const QuestionViewComponent = useMemo(
    () => questionPreviewComponents[question.questionThemeName],
    [question.questionThemeName]
  )
  const isSingleChoiceTheme = useMemo(
    () => singleChoiceThemes.includes(question.questionThemeName),
    [question.questionThemeName]
  )
  // Merge the local (unsaved or just saved) changes into the values, so controlled inputs like selects reflect them
  const values = useMemo(() => {
    if (!Array.isArray(valueInfo.values)) {
      return valueInfo.values
    }

    return valueInfo.values.map((valueItem) =>
      Object.prototype.hasOwnProperty.call(value, valueItem.key)
        ? { ...valueItem, value: value[valueItem.key] }
        : valueItem
    )
  }, [valueInfo.values, value])
  // valueInfo.values may be a new array on every parent render, so compare by content
  const valuesSignature = useMemo(
    () => JSON.stringify(valueInfo.values ?? null),
    [valueInfo.values]
  )

  useEffect(() => {
    const children = isSingleChoiceTheme
      ? question.answers
      : question.subquestions

    setChildren(children)
  }, [question.answers, question.subquestions, language])

  // Drop local overrides once refreshed response values arrive from the parent
  useEffect(() => {
    setPending(emptyPending)
  }, [valuesSignature])

  // Only after all hooks, so every render calls them in the same order
  if (!QuestionViewComponent) {
    return (
      <h6 className="pb-5">
        {t(
          `This question type isn't supported in the LimeSurvey Editor, so the detail view can't be shown. Your response is still collected.`
        )}
      </h6>
    )
  }

  /**
   * Keeps a changed response value until it is confirmed.
   *
   * @param {*} newValue The new value
   * @param {string} key The response field
   * @param {boolean} [isInvalid] True when the input can't be saved (e.g. a date outside its limits): Drops the pending change and marks the field invalid
   * @returns {void}
   */
  const handleOnValueChange = (newValue, key, isInvalid = false) => {
    if (isInvalid) {
      setPending((previous) => {
        // eslint-disable-next-line no-unused-vars
        const { [key]: _dropped, ...rest } = previous.value

        return {
          value: rest,
          invalidKeys:
            disableUpdatingResponses || previous.invalidKeys.includes(key)
              ? previous.invalidKeys
              : [...previous.invalidKeys, key],
          isUpdated: Object.keys(rest).length > 0,
        }
      })
      return
    }

    if (disableUpdatingResponses) {
      // Debounce toast messages to prevent spam (only show once per 3 seconds)
      const now = Date.now()
      if (now - lastToastTimeRef.current > 3000) {
        lastToastTimeRef.current = now
        Toast({
          message: t("You don't have permission to update this response."),
          position: 'bottom-center',
          className: 'generic-toast',
          duration: 3000,
        })
      }
      return
    }

    setPending((previous) => ({
      value: { ...previous.value, [key]: newValue },
      invalidKeys: previous.invalidKeys.filter(
        (invalidKey) => invalidKey !== key
      ),
      isUpdated: true,
    }))
  }

  /**
   * Saves the pending changes, unless an input can't be saved.
   *
   * @returns {void}
   */
  const handleOnSave = () => {
    if (disableUpdatingResponses || invalidKeys.length > 0) {
      return
    }

    setPending((previous) => ({ ...previous, isUpdated: false }))
    onSave(value)
    Toast({
      message: t('Responses updated!'),
      position: 'bottom-center',
      className: 'success-toast',
    })
  }

  return (
    <div
      className={classNames('question-body', {
        'disabled opacity-50': isQuestionDisabled,
      })}
    >
      <QuestionViewComponent
        question={question}
        language={language}
        isHovered={isHovered}
        surveySettings={surveySettings}
        _children={children}
        values={values}
        participantMode={true}
        onValueChange={handleOnValueChange}
      />
      <div
        className={classNames('d-flex justify-content-end gap-2 mt-3', {
          'opacity-0 disabled pointer-events-none': !showButtons,
        })}
      >
        <Button
          size="lg"
          className="text-light"
          variant="secondary"
          onClick={onCancel}
        >
          {t('Cancel')}
        </Button>
        <Button
          size="lg"
          className="text-light"
          variant="danger"
          onClick={handleOnSave}
          disabled={invalidKeys.length > 0}
        >
          {t('Confirm')}
        </Button>
      </div>
    </div>
  )
}
