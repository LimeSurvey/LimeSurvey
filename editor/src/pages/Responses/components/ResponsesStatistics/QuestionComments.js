import { useMemo, useState } from 'react'

import { useQuestionComments } from 'hooks'
import { dayJsHelper } from 'helpers'

import { StatisticsDetailModal } from './StatisticsDetailModal.js'
import { StatisticsFilterSelect } from './StatisticsFilterSelect.js'

const NON_ANSWER_KEYS = ['comment', 'other']
const PREVIEW_LIMIT = 5

export const QuestionComments = ({
  surveyId,
  questionCode,
  qid,
  answerOptions = [],
  questionTitle = '',
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

      <StatisticsDetailModal
        show={showModal}
        onHide={() => setShowModal(false)}
        modalClassname="responses-comments-modal"
        title={questionTitle}
      >
        <div className="responses-comments">
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
        </div>
      </StatisticsDetailModal>
    </div>
  )
}
