import { useEffect, useMemo, useRef, useState } from 'react'
import classNames from 'classnames'

import { useAppState, useBuffer, useErrors } from 'hooks'
import {
  isTrue,
  L10ns,
  createBufferOperation,
  Entities,
  hasTempId,
  STATES,
} from 'helpers'
import { ContentEditor } from 'components/UIComponents'
import { ArrowRightIcon } from 'components/icons'
import { hasQuestionCondition } from 'components/ConditionDesigner/utils'

import { TestValidation } from './QuestionSchema'

export const QuestionHeader = ({
  handleUpdate,
  language,
  question,
  onError = () => {},
  isFocused = false,
  questionNumber,
  setIsTitleFocused,
}) => {
  const { addToBuffer } = useBuffer()
  const [errors, setErrors] = useState('')
  const { getError } = useErrors()

  const titleRef = useRef(null)
  const [codeToQuestion] = useAppState(STATES.CODE_TO_QUESTION)
  const [attributeDescriptions] = useAppState(STATES.ATTRIBUTE_DESCRIPTIONS)

  const focusTitle = useMemo(
    () => isFocused && hasTempId(question.qid),
    [isFocused]
  )

  const updateTitle = (updated) => {
    if (!question.l10ns[language]) {
      question.l10ns[language] = {}
    }

    question.l10ns[language].question = updated.question

    const operation = createBufferOperation(question.qid)
      .questionL10n()
      .update({ [language]: { question: updated.question } })

    handleUpdate({ question })
    addToBuffer(operation)
  }

  const updateDescription = (updated) => {
    if (!question.l10ns[language]) {
      question.l10ns[language] = {}
    }

    question.l10ns[language].help = updated.help
    const operation = createBufferOperation(question.qid)
      .questionL10n()
      .update({ [language]: { help: updated.help } })

    handleUpdate({ question })
    addToBuffer(operation)
  }

  useEffect(() => {
    onError(errors)
  }, [errors, onError])

  const questionTitle = L10ns({
    prop: 'question',
    language,
    l10ns: question.l10ns,
  })

  const questionDescription = L10ns({
    prop: 'help',
    language,
    l10ns: question.l10ns,
  })

  return (
    <div
      data-testid="question-header"
      className="question-header d-flex flex-row"
    >
      <div className="questoin-header-title-container d-flex flex-column">
        <div className="question-title d-flex align-items-center">
          <div className="question-number d-flex align-items-center">
            <div data-testid="question-number">{questionNumber}</div>
            <ArrowRightIcon className="text-primary fill-current" />
          </div>
          <h2
            className="title-default-heading"
            data-error={getError(question.qid, Entities.questionL10n)}
          >
            <ContentEditor
              testId="question-content-editor"
              className={classNames(
                question.attributes?.cssclass?.value,
                { 'error-focus': errors },
                'question-title'
              )}
              innerRef={titleRef}
              placeholder={t('Your question here')}
              useRichTextEditor={!process.env.STORYBOOK_DEV}
              update={(question) => updateTitle({ question })}
              value={questionTitle}
              testValidation={TestValidation}
              setErrors={setErrors}
              language={language}
              replaceVariables={true}
              showToolbar={true}
              noPermissionDisabled={true}
              showToolTip={false}
              focus={focusTitle}
              onFocus={() => setIsTitleFocused(true)}
              onBlur={() => setIsTitleFocused(false)}
              questionNumber={questionNumber}
              codeToQuestion={codeToQuestion}
              attributeDescriptions={attributeDescriptions}
            />
          </h2>
          {hasQuestionCondition(question) && (
            <span
              className="ms-2 condition-badge"
              onClick={() => {
                const scrollInterval = setInterval(() => {
                  if (
                    window.location.href.includes(`?question=${question.qid}`)
                  ) {
                    const target = document.querySelector('#condition-designer')
                    if (target) {
                      target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                      })
                      clearInterval(scrollInterval)
                    }
                  }
                }, 100)
              }}
              style={{ cursor: 'pointer' }}
            >
              C
            </span>
          )}
          {isTrue(question.mandatory) ? (
            <span className="ms-2" style={{ fontSize: '24px' }}>
              *
            </span>
          ) : (
            ''
          )}
        </div>
        <div
          className={classNames(
            'question-description d-flex align-items-center'
          )}
        >
          <ContentEditor
            className="question-description-content-editor"
            testId={'question-help-description-content-editor'}
            placeholder={t('Optional help description')}
            update={(help) => updateDescription({ help })}
            value={questionDescription}
            noPermissionDisabled={true}
            showToolTip={false}
          />
        </div>
      </div>
    </div>
  )
}
