import { useEffect, useRef } from 'react'
import { Draggable } from 'react-beautiful-dnd'
import classNames from 'classnames'
import { SubquestionCodeInput } from '../subquestionCodeComponents'
import { useSurvey, useAppState } from 'hooks'
import { Entities, STATES } from 'helpers'
import { useParams } from 'react-router-dom'
import { ContentEditor } from 'components/UIComponents'
import { CloseCircleFillIcon, DragIcon } from 'components/icons'
import { L10ns } from 'helpers'

export const RankingAdvancedQuestionAnswers = ({
  handleAnswerUpdate = () => {},
  handleRemoveAnswer = () => {},
  isFocused,
  setAnswersHeight,
  answers = [],
  qid,
  language,
  handleCodeUpdate,
}) => {
  const answersRef = useRef(null)
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)

  useEffect(() => {
    if (!setAnswersHeight) {
      return
    }

    const answersHeight = []
    const observer = new ResizeObserver(() => {
      if (!answersRef.current) {
        return
      }

      answersRef.current
        .querySelectorAll('.ranking-advanced-answer-content-editor')
        .forEach((item, index) => {
          answersHeight[index] = item.offsetHeight
        })

      setAnswersHeight([...answersHeight])
    })

    if (answersRef.current) {
      answersRef.current
        .querySelectorAll('.ranking-advanced-answer-content-editor')
        .forEach((item) => {
          observer.observe(item)
        })
      observer.observe(answersRef.current)
    }

    return () => {
      observer.disconnect()
    }
  }, [])

  return (
    <div>
      {answers.map((answer, index) => {
        return (
          <Draggable
            key={`advanced-answer${answer.qid}-${index}`}
            draggableId={`advanced-answer${answer.qid}-${index}`}
            index={index}
          >
            {(provided, snapshot) => {
              return (
                <div {...provided.draggableProps} ref={provided.innerRef}>
                  <div
                    className={classNames(
                      'position-relative ranking-advanced-answer w-100 ps-0 p-1 d-flex align-items-center question-body-content remove-option-button-parent'
                    )}
                  >
                    <div
                      className={classNames(
                        'cursor-pointer position-absolute remove-option-button',
                        {
                          'd-none disabled': !isFocused,
                        }
                      )}
                      onClick={() => handleRemoveAnswer(answer)}
                      style={{ left: -24 }}
                    >
                      <CloseCircleFillIcon
                        className={classNames(
                          'text-danger fill-current d-block'
                        )}
                        style={{ height: 14 }}
                      />
                    </div>
                    <div className="d-flex align-items-center gap-5">
                      {isFocused && survey.showQNumCode?.showNumber && (
                        <SubquestionCodeInput
                          isSurveyActive={isSurveyActive}
                          code={answer.code}
                          onChange={(e) =>
                            handleCodeUpdate({
                              newCode: e.target.value,
                              childIndex: index,
                              childArray: answers,
                              entityType: Entities.answer,
                              entityTitleKey: 'answer',
                            })
                          }
                        />
                      )}
                      <ContentEditor
                        key={`Ranking-Advanced-Answer-${qid}`}
                        value={L10ns({
                          l10ns: answer.l10ns,
                          language,
                          prop: 'answer',
                        })}
                        update={(value) => handleAnswerUpdate(value, index)}
                        placeholder={t('Answer option')}
                        className={classNames(
                          'choice ranking-advanced-answer-content-editor w-100',
                          {
                            'focus-element': snapshot.isDragging,
                          }
                        )}
                        testId="ranking-advanced-answer-content-editor"
                      />
                    </div>
                    <div
                      {...provided.dragHandleProps}
                      style={{
                        position: 'absolute',
                        right: '0',
                        transform: 'translate(-50%, -50%)',
                        height: '14px',
                        width: '14px',
                      }}
                      onClick={(e) => e.stopPropagation()}
                    >
                      <DragIcon />
                    </div>
                  </div>
                </div>
              )
            }}
          </Draggable>
        )
      })}
    </div>
  )
}
