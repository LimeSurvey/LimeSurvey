import React, { useEffect, useMemo, useState } from 'react'

import { useQuestionComments } from 'hooks'
import { dayJsHelper } from 'helpers'

import { StatisticsDetailModal } from './StatisticsDetailModal.js'
import { StatisticsFilterSelect } from './StatisticsFilterSelect.js'
import {
  CommentSwatch,
  buildOptionByAnswer,
  getAnswerFilterOptions,
} from './ChartsUtils.js'

export const CommentsModal = ({
  show,
  onHide,
  surveyId,
  questionCode,
  questionTitle = '',
  answerOptions = [],
  initialAnswer = '',
}) => {
  const [selectedAnswer, setSelectedAnswer] = useState(initialAnswer)

  // Re-sync when opened from a different bar (or reset by the caller).
  useEffect(() => {
    setSelectedAnswer(initialAnswer)
  }, [initialAnswer])

  const { comments, fetchNextPage, hasNextPage, isFetchingNextPage } =
    useQuestionComments(surveyId, questionCode, { enabled: show })

  const options = useMemo(
    () => getAnswerFilterOptions(answerOptions),
    [answerOptions]
  )
  const optionByAnswer = useMemo(
    () => buildOptionByAnswer(answerOptions),
    [answerOptions]
  )

  const visibleComments = useMemo(() => {
    if (!selectedAnswer) return comments
    const selectedOption = options.find((option) => option.key === selectedAnswer)
    const matches = [selectedAnswer, selectedOption?.title].filter(Boolean)
    return comments.filter((comment) => matches.includes(comment.subQuestion))
  }, [comments, selectedAnswer, options])

  return (
    <StatisticsDetailModal
      show={show}
      onHide={onHide}
      modalClassname="responses-statistics-comments-modal"
    >
      <div className="responses-statistics-comments">
        <h2 className="responses-statistics-modal-title">{questionTitle}</h2>
        {options.length > 0 && (
          <StatisticsFilterSelect
            label={t('See all comments for:')}
            options={options}
            value={selectedAnswer}
            onChange={setSelectedAnswer}
            allOption={{ label: t('All answers') }}
          />
        )}
        {visibleComments.length ? (
          <div className="responses-statistics-comments-blocks">
            {visibleComments.map((comment, index) => (
              <div
                className="responses-statistics-comments-block"
                key={`${comment.responseId}-${index}`}
              >
                <span className="responses-statistics-comments-block-main">
                  {!selectedAnswer && (
                    <CommentSwatch
                      fill={optionByAnswer[comment.subQuestion]?.fill}
                    />
                  )}
                  {comment.comment}
                </span>
                {comment.date && (
                  <span className="responses-statistics-comments-block-time">
                    {dayJsHelper(comment.date).fromNow()}
                  </span>
                )}
              </div>
            ))}
          </div>
        ) : (
          <div className="responses-statistics-comments-status">
            {selectedAnswer
              ? t('No comments for this answer.')
              : t('No comments for this question.')}
          </div>
        )}
        {hasNextPage && (
          <div className="responses-statistics-comments-more">
            <button
              type="button"
              className="responses-statistics-comments-more-btn"
              onClick={() => fetchNextPage()}
              disabled={isFetchingNextPage}
            >
              {isFetchingNextPage ? t('Loading...') : t('Load more')}
            </button>
          </div>
        )}
      </div>
    </StatisticsDetailModal>
  )
}
