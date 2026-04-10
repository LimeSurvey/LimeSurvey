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

export const RankingAdvancedQuestionSubquestions = ({
  handleSubquestionUpdate = () => {},
  handleRemoveSubquestion = () => {},
  isFocused,
  setSubQuestionsHeight,
  subquestions = [],
  qid,
  language,
  handleCodeUpdate,
}) => {
  const subquestionsRef = useRef(null)
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)

  useEffect(() => {
    if (!setSubQuestionsHeight) {
      return
    }

    const subquestionsHeight = []
    const observer = new ResizeObserver(() => {
      if (!subquestionsRef.current) {
        return
      }

      subquestionsRef.current
        .querySelectorAll('.ranking-advanced-subquestion-content-editor')
        .forEach((item, index) => {
          subquestionsHeight[index] = item.offsetHeight
        })

      setSubQuestionsHeight([...subquestionsHeight])
    })

    if (subquestionsRef.current) {
      subquestionsRef.current
        .querySelectorAll('.ranking-advanced-subquestion-content-editor')
        .forEach((item) => {
          observer.observe(item)
        })
      observer.observe(subquestionsRef.current)
    }

    return () => {
      observer.disconnect()
    }
  }, [])

  return (
    <div>
      {subquestions.map((subquestion, index) => {
        return (
          <Draggable
            key={`advanced-subquestion${subquestion.qid}-${index}`}
            draggableId={`advanced-subquestion${subquestion.qid}-${index}`}
            index={index}
          >
            {(provided, snapshot) => {
              return (
                <div {...provided.draggableProps} ref={provided.innerRef}>
                  <div
                    className={classNames(
                      'position-relative ranking-advanced-subquestion w-100 ps-0 p-1 d-flex align-items-center question-body-content remove-option-button-parent'
                    )}
                  >
                    <div
                      className={classNames(
                        'cursor-pointer position-absolute remove-option-button',
                        {
                          'd-none disabled': !isFocused,
                        }
                      )}
                      onClick={() => handleRemoveSubquestion(subquestion)}
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
                          code={subquestion.title}
                          onChange={(e) =>
                            handleCodeUpdate({
                              newCode: e.target.value,
                              childIndex: index,
                              childArray: subquestions,
                              entityType: Entities.subquestion,
                              entityTitleKey: 'question',
                            })
                          }
                        />
                      )}
                      <ContentEditor
                        key={`Ranking-Advanced-Subquestion-${qid}`}
                        value={L10ns({
                          l10ns: subquestion.l10ns,
                          language,
                          prop: 'question',
                        })}
                        update={(value) => handleSubquestionUpdate(value, index)}
                        placeholder={t('Subquestion option')}
                        className={classNames(
                          'choice ranking-advanced-subquestion-content-editor',
                          {
                            'focus-element': snapshot.isDragging,
                          }
                        )}
                        testId="ranking-advanced-subquestion-content-editor"
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
