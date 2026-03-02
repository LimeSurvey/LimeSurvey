import classNames from 'classnames'
import ReactContentEditable from 'react-contenteditable'

import { isTrue, L10ns } from 'helpers'
import { ArrowRightIcon } from 'components/icons'

export const QuestionHeaderPreview = ({
  language,
  question,
  questionNumber,
}) => {
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
          <h2 className="title-default-heading content-editor">
            <ReactContentEditable
              disabled={true}
              html={questionTitle?.toString()}
              placeholder={t('Your question here')}
            />
          </h2>
          {question.scenarios?.length > 0 && (
            <span className="ms-2 condition-badge">C</span>
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
          <ReactContentEditable
            className="content-editor question-description-content-editor"
            disabled={true}
            html={questionDescription?.toString()}
            placeholder={t('Optional help description')}
          />
        </div>
      </div>
    </div>
  )
}
