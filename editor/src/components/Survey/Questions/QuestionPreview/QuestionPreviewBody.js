import { useEffect, useMemo, useState, useRef } from 'react'
import classNames from 'classnames'

import {
  questionPreviewComponents,
  singleChoiceThemes,
} from '../../../QuestionTypes'
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
  const [valueIsUpdated, setValueIsUpdated] = useState(false)
  const [value, setValue] = useState({})
  const lastToastTimeRef = useRef(0)

  const QuestionViewComponent = useMemo(
    () => questionPreviewComponents[question.questionThemeName],
    [question.questionThemeName]
  )
  const isSingleChoiceTheme = useMemo(
    () => singleChoiceThemes.includes(question.questionThemeName),
    [question.questionThemeName]
  )

  if (!QuestionViewComponent) {
    return (
      <h6 className="pb-5">
        {t(
          `This question type isn't supported in the Limesurvey Editor, so the detail view can't be shown. Your response is still collected.`
        )}
      </h6>
    )
  }

  useEffect(() => {
    const children = isSingleChoiceTheme
      ? question.answers
      : question.subquestions

    setChildren(children)
  }, [question.answers, question.subquestions, language])

  const handleOnValueChange = (newValue, key) => {
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

    setValue({ ...value, [key]: newValue })
    setValueIsUpdated(true)
  }

  const handleOnSave = () => {
    if (disableUpdatingResponses) {
      return
    }

    setValueIsUpdated(false)
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
        values={valueInfo.values}
        participantMode={true}
        onValueChange={handleOnValueChange}
      />
      <div
        className={classNames('d-flex justify-content-end gap-2 mt-3', {
          'opacity-0 disabled pointer-events-none': !valueIsUpdated,
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
        >
          {t('Confirm')}
        </Button>
      </div>
    </div>
  )
}
