import { useMemo } from 'react'

import { useQuestionComments } from 'hooks'

import { CommentSwatch, buildOptionByAnswer } from './ChartsUtils.js'

const PREVIEW_LIMIT = 5

export const QuestionComments = ({
  surveyId,
  questionCode,
  answerOptions = [],
  onViewComments,
}) => {
  const { comments, isLoading } = useQuestionComments(surveyId, questionCode)

  const optionByAnswer = useMemo(
    () => buildOptionByAnswer(answerOptions),
    [answerOptions]
  )

  const renderTable = (items) => (
    <table className="responses-statistics-comments-table">
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
              <CommentSwatch fill={optionByAnswer[comment.subQuestion]?.fill} />
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

  if (isLoading) {
    return (
      <div className="responses-statistics-comments">
        <div className="responses-statistics-comments-status">
          <span className="loader"></span>
        </div>
      </div>
    )
  }

  if (!comments.length) {
    return (
      <div className="responses-statistics-comments">
        <div className="responses-statistics-comments-status">
          {t('No comments for this question.')}
        </div>
      </div>
    )
  }

  return (
    <div className="responses-statistics-comments">
      {renderTable(comments.slice(0, PREVIEW_LIMIT))}
      <div className="responses-statistics-comments-more">
        <button
          type="button"
          className="responses-statistics-comments-more-btn"
          onClick={() => onViewComments?.('')}
        >
          {t('Show all')}
        </button>
      </div>
    </div>
  )
}
