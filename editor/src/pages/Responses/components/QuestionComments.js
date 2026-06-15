import { useMemo, useState } from 'react'

import { ComponentModal } from 'components'
import { useQuestionComments } from 'hooks'
import { dayJsHelper } from 'helpers'

// Pseudo entries the chart adds that are not real, filterable answer options.
const NON_ANSWER_KEYS = ['comment', 'other']
// Rows shown in the inline table before the user opens the full modal.
const PREVIEW_LIMIT = 5

export const QuestionComments = ({
  surveyId,
  questionCode,
  qid,
  answerOptions = [],
}) => {
  const [selectedAnswer, setSelectedAnswer] = useState('')
  const [showModal, setShowModal] = useState(false)

  const { comments, fetchNextPage, hasNextPage, isLoading, isFetchingNextPage } =
    useQuestionComments(surveyId, questionCode, { qid })

  const options = useMemo(
    () =>
      answerOptions.filter(
        (option) => option?.key && !NON_ANSWER_KEYS.includes(option.key)
      ),
    [answerOptions]
  )

  // Map each answer option to the color it is drawn with in the chart so a
  // comment can show the swatch of the answer it belongs to. A comment's
  // subQuestion carries the subquestion text, so we key by title (and fall
  // back to key) to resolve the option.
  const optionByAnswer = useMemo(() => {
    const map = {}
    answerOptions.forEach((option) => {
      if (option?.title != null) map[option.title] = option
      if (option?.key != null && !(option.key in map)) map[option.key] = option
    })
    return map
  }, [answerOptions])

  // The comments endpoint is paginated server-side over all comment answers, so
  // we narrow to the picked answer here by matching the comment's subQuestion
  // text against the selected option.
  const visibleComments = useMemo(() => {
    if (!selectedAnswer) return comments
    const selectedOption = options.find((option) => option.key === selectedAnswer)
    const matches = [selectedAnswer, selectedOption?.title].filter(Boolean)
    return comments.filter((comment) => matches.includes(comment.subQuestion))
  }, [comments, selectedAnswer, options])

  const renderSwatch = (subQuestion) => {
    const fill = optionByAnswer[subQuestion]?.fill
    if (!fill) return null
    return (
      <span
        className="responses-comments-swatch"
        style={{ backgroundColor: fill }}
      />
    )
  }

  const renderTable = (items) => (
    <table className="responses-comments-table">
      <thead>
        <tr>
          <th>{t('Answer option')}</th>
          <th>{t('Comment')}</th>
        </tr>
      </thead>
      <tbody>
        {items.map((comment, index) => (
          <tr key={`${comment.responseId}-${index}`}>
            <td>
              {renderSwatch(comment.subQuestion)}
              {optionByAnswer[comment.subQuestion]?.title ||
                comment.subQuestion ||
                ''}
            </td>
            <td>{comment.comment}</td>
          </tr>
        ))}
      </tbody>
    </table>
  )

  const renderBlocks = (items) => (
    <div className="responses-comments-blocks">
      {items.map((comment, index) => (
        <div
          className="responses-comments-block"
          key={`${comment.responseId}-${index}`}
        >
          <span className="responses-comments-block-main">
            {renderSwatch(comment.subQuestion)}
            {comment.comment}
          </span>
          {comment.date && (
            <span className="responses-comments-block-time">
              {dayJsHelper(comment.date).fromNow()}
            </span>
          )}
        </div>
      ))}
    </div>
  )

  const renderInline = () => {
    if (isLoading) {
      return (
        <div className="responses-comments-status">
          <span className="loader"></span>
        </div>
      )
    }

    if (!comments.length) {
      return (
        <div className="responses-comments-status">
          {t('No comments for this question.')}
        </div>
      )
    }

    return (
      <>
        {renderTable(comments.slice(0, PREVIEW_LIMIT))}
        <div className="responses-comments-more">
          <button
            type="button"
            className="responses-comments-more-btn"
            onClick={() => setShowModal(true)}
          >
            {t('Show more')}
          </button>
        </div>
      </>
    )
  }

  return (
    <div className="responses-comments">
      {renderInline()}

      <ComponentModal
        show={showModal}
        onHide={() => setShowModal(false)}
        modalClassname="responses-comments-modal"
        componentClassname="responses-comments responses-comments-modal-body"
        Component={
          <>
            {options.length > 0 && (
              <select
                className="responses-comments-filter"
                value={selectedAnswer}
                onChange={(event) => setSelectedAnswer(event.target.value)}
              >
                <option value="">{t('All answers')}</option>
                {options.map((option) => (
                  <option key={option.key} value={option.key}>
                    {option.title || option.label || option.key}
                  </option>
                ))}
              </select>
            )}
            {visibleComments.length ? (
              renderBlocks(visibleComments)
            ) : (
              <div className="responses-comments-status">
                {t('No comments for this answer.')}
              </div>
            )}
            {hasNextPage && (
              <div className="responses-comments-more">
                <button
                  type="button"
                  className="responses-comments-more-btn"
                  onClick={() => fetchNextPage()}
                  disabled={isFetchingNextPage}
                >
                  {isFetchingNextPage ? t('Loading...') : t('Load more')}
                </button>
              </div>
            )}
          </>
        }
      />
    </div>
  )
}
