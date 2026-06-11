import { useMemo, useState } from 'react'

import { useQuestionComments } from 'hooks'

// Pseudo entries the chart adds that are not real, filterable answer options.
const NON_ANSWER_KEYS = ['comment', 'other']

export const QuestionComments = ({
  surveyId,
  questionCode,
  qid,
  answerOptions = [],
  display = 'block',
}) => {
  const [selectedAnswer, setSelectedAnswer] = useState('')

  const { comments, fetchNextPage, hasNextPage, isLoading, isFetchingNextPage } =
    useQuestionComments(surveyId, questionCode, { qid, selectedAnswer })

  const options = useMemo(
    () =>
      answerOptions.filter(
        (option) => option?.key && !NON_ANSWER_KEYS.includes(option.key)
      ),
    [answerOptions]
  )

  const showFilter = display === 'block' && options.length > 0

  const renderBody = () => {
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

    if (display === 'table') {
      return (
        <table className="responses-comments-table">
          <thead>
            <tr>
              <th>{t('Answer option')}</th>
              <th>{t('Comment')}</th>
            </tr>
          </thead>
          <tbody>
            {comments.map((comment, index) => (
              <tr key={`${comment.responseId}-${index}`}>
                <td>{comment.comment}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )
    }

    return (
      <div className="responses-comments-blocks">
        {comments.map((comment, index) => (
          <div
            className="responses-comments-block"
            key={`${comment.responseId}-${index}`}
          >
            {comment.comment}
          </div>
        ))}
      </div>
    )
  }

  return (
    <div className="responses-comments">
      {showFilter && (
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
      {renderBody()}
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
    </div>
  )
}
