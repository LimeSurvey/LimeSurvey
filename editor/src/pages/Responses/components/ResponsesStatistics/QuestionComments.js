import { useMemo, useState } from 'react'

import { useQuestionComments } from 'hooks'
import { dayJsHelper } from 'helpers'

import { StatisticsDetailModal } from './StatisticsDetailModal.js'
import { StatisticsFilterSelect } from './StatisticsFilterSelect.js'

const NON_ANSWER_KEYS = ['comment', 'other']
const PREVIEW_LIMIT = 5
// Types whose comment is a single question-wide field (not per answer/sub-
// question), so the answer filter doesn't apply. 'O' = list with comment.
const QUESTION_WIDE_COMMENT_TYPES = ['O']

export const QuestionComments = ({
  surveyId,
  questionCode,
  answerOptions = [],
  questionTitle = '',
  questionType = '',
}) => {
  // Per-answer comment types (e.g. 'P') can be filtered by answer; question-wide
  // ones ('O') cannot, so the answer select is hidden for them.
  const isPerAnswer = !QUESTION_WIDE_COMMENT_TYPES.includes(questionType)
  const [selectedAnswer, setSelectedAnswer] = useState('')
  const [showModal, setShowModal] = useState(false)

  const { comments, fetchNextPage, hasNextPage, isLoading, isFetchingNextPage } =
    useQuestionComments(surveyId, questionCode)

  const options = useMemo(
    () =>
      answerOptions.filter(
        (option) => option?.key && !NON_ANSWER_KEYS.includes(option.key)
      ),
    [answerOptions]
  )

  const optionByAnswer = useMemo(() => {
    const map = {}
    answerOptions.forEach((option) => {
      if (option?.title != null) map[option.title] = option
      if (option?.key != null && !(option.key in map)) map[option.key] = option
    })
    return map
  }, [answerOptions])

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
        className="responses-statistics-comments-swatch"
        style={{ backgroundColor: fill }}
      />
    )
  }

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
    <div className="responses-statistics-comments-blocks">
      {items.map((comment, index) => (
        <div
          className="responses-statistics-comments-block"
          key={`${comment.responseId}-${index}`}
        >
          <span className="responses-statistics-comments-block-main">
            {renderSwatch(comment.subQuestion)}
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
  )

  const renderInline = () => {
    if (isLoading) {
      return (
        <div className="responses-statistics-comments-status">
          <span className="loader"></span>
        </div>
      )
    }

    if (!comments.length) {
      return (
        <div className="responses-statistics-comments-status">
          {t('No comments for this question.')}
        </div>
      )
    }

    return (
      <>
        {renderTable(comments.slice(0, PREVIEW_LIMIT))}
        <div className="responses-statistics-comments-more">
          <button
            type="button"
            className="responses-statistics-comments-more-btn"
            onClick={() => setShowModal(true)}
          >
            {t('Show more')}
          </button>
        </div>
      </>
    )
  }

  return (
    <div className="responses-statistics-comments">
      {renderInline()}

      <StatisticsDetailModal
        show={showModal}
        onHide={() => setShowModal(false)}
        modalClassname="responses-statistics-comments-modal"
        title={questionTitle}
      >
        <div className="responses-statistics-comments">
          {isPerAnswer && options.length > 0 && (
            <StatisticsFilterSelect
              label={t('See all comments for:')}
              options={options}
              value={selectedAnswer}
              onChange={setSelectedAnswer}
              allOption={{ label: t('All answers') }}
            />
          )}
          {visibleComments.length ? (
            renderBlocks(visibleComments)
          ) : (
            <div className="responses-statistics-comments-status">
              {isPerAnswer
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
    </div>
  )
}
