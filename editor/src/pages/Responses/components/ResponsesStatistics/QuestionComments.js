import { useMemo } from 'react'

import { useQuestionComments } from 'hooks'
import { LSTable } from 'components'
import { htmlToPlainText } from 'helpers'

import { CommentSwatch, buildOptionByAnswer } from './ChartsUtils.js'

const PREVIEW_LIMIT = 5

export const QuestionComments = ({
  surveyId,
  questionCode,
  fields,
  answerOptions = [],
  onViewComments,
}) => {
  const { comments, isLoading } = useQuestionComments(surveyId, questionCode, {
    fields,
  })

  const optionByAnswer = useMemo(
    () => buildOptionByAnswer(answerOptions),
    [answerOptions]
  )

  const columns = useMemo(
    () => [
      {
        id: 'answer',
        header: t('Answer option'),
        cell: ({ row }) => (
          <>
            <CommentSwatch
              fill={optionByAnswer[row.original.subQuestion]?.fill}
            />
            {optionByAnswer[row.original.subQuestion]?.title ||
              row.original.subQuestion ||
              ''}
          </>
        ),
      },
      {
        id: 'comment',
        header: t('Comment'),
        cell: ({ row }) => htmlToPlainText(row.original.comment),
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
      <LSTable columns={columns} data={previewRows} />
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
