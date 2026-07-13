import { useMemo } from 'react'

import { useQuestionComments } from 'hooks'
import { LSTable } from 'components'

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

  const columns = useMemo(
    () => [
      {
        key: 'answer',
        title: t('Answer option'),
        render: (comment) => (
          <>
            <CommentSwatch fill={optionByAnswer[comment.subQuestion]?.fill} />
            {optionByAnswer[comment.subQuestion]?.title ||
              comment.subQuestion ||
              ''}
          </>
        ),
      },
      {
        key: 'comment',
        title: t('Comment'),
        render: (comment) => comment.comment,
      },
    ],
    [optionByAnswer]
  )

  const previewRows = useMemo(
    () =>
      comments.slice(0, PREVIEW_LIMIT).map((comment, index) => ({
        ...comment,
        id: `${comment.responseId}-${index}`,
      })),
    [comments]
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
      <LSTable columns={columns} data={previewRows} rowId="id" />
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
