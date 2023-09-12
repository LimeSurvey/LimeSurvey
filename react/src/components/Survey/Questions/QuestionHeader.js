import { useEffect, useRef, useState } from 'react'
import classNames from 'classnames'

import { IsTrue, L10ns, RemoveHTMLTagsInString } from 'helpers'
import { ContentEditor } from 'components/UIComponents'
import { TestValidation } from './QuestionSchema'
import { ArrowRightIcon } from 'components/icons'

export const QuestionHeader = ({
  handleUpdate,
  language,
  question,
  questionNumber,
  onError = () => {},
  isFocused,
}) => {
  const [errors, setErrors] = useState('')
  const [useRichTextEditor, setUseRichTextEditor] = useState(false)

  const indexRef = useRef(null)
  const titleRef = useRef(null)
  const [isMandatory, setIsMandatory] = useState(false)

  const updateTitle = (updated) => {
    question.l10ns[language].question = updated.question

    handleUpdate({ question })
  }

  const updateDescription = (updated) => {
    question.l10ns[language].help = updated.help
    handleUpdate({ question })
  }

  useEffect(() => {
    onError(errors)
  }, [errors, onError])

  useEffect(() => {
    setIsMandatory(IsTrue(question.mandatory))
  }, [question.mandatory])

  useEffect(() => {
    setUseRichTextEditor(isFocused)
  }, [isFocused])

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

  const titleWithoutHtml = RemoveHTMLTagsInString(questionTitle)
  const descriptionWithoutHtml = RemoveHTMLTagsInString(questionDescription)

  return (
    <div
      onMouseEnter={() => {
        setUseRichTextEditor(true)
      }}
      onMouseLeave={() => {
        if (!isFocused) setUseRichTextEditor(false)
      }}
      className="question-header d-flex flex-column"
    >
      <div className="question-title d-flex align-items-center p-3">
        <div
          className="d-flex align-items-center gap-1 question-number"
          ref={indexRef}
        >
          <div>{questionNumber}</div>
          <ArrowRightIcon className="text-primary fill-current" />
        </div>
        <h2>
          <ContentEditor
            className={classNames(
              'p-0 m-0',
              question.attributes?.cssclass?.value,
              {
                'error-focus': errors,
              }
            )}
            innerRef={titleRef}
            placeholder={"What's your question?"}
            useRichTextEditor={useRichTextEditor}
            update={(question) => updateTitle({ question })}
            value={questionTitle}
            testValidation={TestValidation}
            setErrors={setErrors}
            focus={question.tempFocusTitle}
            style={{
              width: titleWithoutHtml ? 'fit-content' : '300px',
            }}
            language={language}
            replaceVariables={true}
            isFocused={isFocused}
          />
        </h2>
        {isMandatory ? (
          <span className="text-danger ms-2" style={{ fontSize: '24px' }}>
            *
          </span>
        ) : (
          ''
        )}
      </div>
      {(isFocused || questionDescription) && (
        <div
          className={classNames(
            'question-description d-flex align-items-center'
          )}
        >
          <ContentEditor
            placeholder="Optional help description"
            update={(help) => updateDescription({ help })}
            value={questionDescription}
            style={{
              width: descriptionWithoutHtml ? 'fit-content' : '250px',
              lineHeight: '16px',
            }}
          />
        </div>
      )}
    </div>
  )
}
