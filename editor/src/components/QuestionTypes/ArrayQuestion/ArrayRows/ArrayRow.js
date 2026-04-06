import React from 'react'
import classNames from 'classnames'
import { useParams } from 'react-router-dom'
import { SubquestionCodeInput } from '../../subquestionCodeComponents'
import { DragIcon, CloseCircleFillIcon } from 'components/icons'
import { useAppState, useSurvey } from 'hooks'
import {
  TooltipContainer,
  ContentEditor,
  getQuestionTypeInfo,
} from 'components'
import { RemoveHTMLTagsInString, STATES } from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { ArraySubQuestionRadioAnswers } from './ArraySubQuestionRadioAnswers'
import { ArraySubQuestionTextAnswers } from './ArraySubQuestionTextAnswers'
import { ArraySubQuestionNumberAnswers } from './ArraySubQuestionNumberAnswers'

export const ArrayRow = ({
  questionThemeName,
  isFocused,
  highestWidth,
  subQuestionsHeight,
  arrayAnswersWidth,
  dragIconSize,
  provided = {},
  index,
  handleUpdateL10ns,
  handleCodeUpdate,
  removeItem,
  showContentEditor,
  showQuestionCode,
  titleValue = '',
  numberOfHorizontalEntities,
  qid,
  placeholder = 'Subquestion',
  itemsKey,
  isNoAnswer = false,
  code,
  scaleId,
}) => {
  const { surveyId } = useParams()

  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const { survey } = useSurvey(surveyId)
  const showQNumCode = survey.showQNumCode
  const isArrayByColumn =
    questionThemeName === getQuestionTypeInfo().ARRAY_COLUMN.theme

  return (
    <div>
      <div
        className={classNames(
          'position-relative d-flex align-items-center array-question-item text-secondary remove-option-button-parent',
          {
            'bg-grey': !(index % 2) && !isArrayByColumn,
          }
        )}
      >
        {!(isSurveyActive && itemsKey === 'subquestions') && (
          <div
            className="cursor-pointer remove-option-button action-item-button remove row"
            onClick={removeItem}
            data-testid="remove-vertical-option-button"
          >
            <CloseCircleFillIcon
              className={classNames('text-danger fill-current', {
                'd-none disabled':
                  !isFocused || !showContentEditor || isNoAnswer,
              })}
            />
          </div>
        )}
        <div>
          {isFocused &&
            showQuestionCode &&
            showQNumCode?.showNumber &&
            scaleId < 1 && (
              <SubquestionCodeInput
                isSurveyActive={isSurveyActive}
                code={code}
                onChange={(e) => handleCodeUpdate(e.target.value, index)}
              />
            )}
        </div>

        <TooltipContainer
          showTip={isSurveyActive}
          tip={getTooltipMessages().ACTIVE_DISABLED}
        >
          <div
            className={classNames('action-item-button drag row', {
              'cursor-not-allowed': isSurveyActive,
            })}
          >
            <div
              {...provided.dragHandleProps}
              className={classNames({
                'd-none': !isFocused || !showContentEditor || isNoAnswer,
                'disabled': isSurveyActive,
              })}
            >
              <DragIcon className="text-secondary fill-current" />
            </div>
          </div>
        </TooltipContainer>
        <div
          style={{
            minWidth: showContentEditor
              ? highestWidth +
                dragIconSize +
                (isNoAnswer && isFocused && showQNumCode?.showNumber ? 80 : 0)
              : '100px',
            maxWidth: !showContentEditor && '100px',
            display: showContentEditor ? 'flex' : 'none',
          }}
          className="ps-3 flex-row justify-content-start"
        >
          <ContentEditor
            placeholder={placeholder}
            value={titleValue}
            update={(value) =>
              handleUpdateL10ns(RemoveHTMLTagsInString(value), index)
            }
            className="array-subquestion-content-editor choice"
            style={{
              width: 'fit-content',
            }}
            disabled={isNoAnswer}
          />
        </div>
        <div className="d-flex" style={{ marginLeft: isFocused ? '-40px' : 0 }}>
          {Array(numberOfHorizontalEntities)
            .fill(1)
            .map((_, _index) => (
              <React.Fragment key={`array-verticalTitle${_index}${qid}`}>
                {questionThemeName === getQuestionTypeInfo().ARRAY.theme && (
                  <ArraySubQuestionRadioAnswers
                    subQuestionindex={index}
                    arrayAnswersWidth={arrayAnswersWidth}
                    dragIconSize={dragIconSize}
                    subQuestionsHeight={subQuestionsHeight}
                    index={_index}
                    qid={qid}
                    isFocused={isFocused}
                  />
                )}
                {questionThemeName ===
                  getQuestionTypeInfo().ARRAY_TEXT.theme && (
                  <ArraySubQuestionTextAnswers
                    arrayAnswersWidth={arrayAnswersWidth}
                    dragIconSize={dragIconSize}
                    subQuestionindex={_index}
                    subQuestionsHeight={subQuestionsHeight}
                    index={_index}
                    isFocused={isFocused}
                  />
                )}
                {questionThemeName ===
                  getQuestionTypeInfo().ARRAY_NUMBERS.theme && (
                  <ArraySubQuestionNumberAnswers
                    arrayAnswersWidth={arrayAnswersWidth}
                    dragIconSize={dragIconSize}
                    subQuestionindex={_index}
                    subQuestionsHeight={subQuestionsHeight}
                    index={_index}
                    isFocused={isFocused}
                  />
                )}
                {questionThemeName ===
                  getQuestionTypeInfo().ARRAY_COLUMN.theme && (
                  <ArraySubQuestionRadioAnswers
                    qid={qid}
                    arrayAnswersWidth={arrayAnswersWidth}
                    dragIconSize={dragIconSize}
                    subQuestionindex={_index}
                    subQuestionsHeight={subQuestionsHeight}
                    index={_index}
                    subQuestionIndex={index}
                    showBackgroundUnderSubQuestion={
                      !(_index % 2) && isArrayByColumn
                    }
                    isFocused={isFocused}
                  />
                )}
                {questionThemeName ===
                  getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme && (
                  <ArraySubQuestionRadioAnswers
                    qid={qid}
                    arrayAnswersWidth={arrayAnswersWidth}
                    dragIconSize={dragIconSize}
                    subQuestionindex={_index}
                    subQuestionsHeight={subQuestionsHeight}
                    index={_index}
                    isFocused={isFocused}
                  />
                )}
              </React.Fragment>
            ))}
        </div>
      </div>
    </div>
  )
}
