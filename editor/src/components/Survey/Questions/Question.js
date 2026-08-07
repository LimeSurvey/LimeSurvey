import classNames from 'classnames'
import { useEffect, useMemo, useRef, useState } from 'react'
import { Button } from 'react-bootstrap'

import { useAppState, useErrors, useFocused } from 'hooks'
import {
  getDisabledQuestionTypes,
  Entities,
  getAttributeValue,
  isTrue,
  ScrollToElement,
  STATES,
} from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { getQuestionImageObjectFromImageAttribute } from 'helpers/questionImage'
import { ArrowDownIcon, ArrowUpIcon } from 'components/icons'
import { QuestionSkeleton, TooltipContainer } from 'components'
import { useIsInViewport } from 'hooks/useInViewport'

import { QuestionHeader } from './QuestionHeader'
import { QuestionBody } from './QuestionBody'
import { QuestionFooter } from './QuestionFooter'
import { getQuestionTypeInfo } from '../../QuestionTypes'
import { QuestionContainer } from './QuestionContainer'

const isInTestMode = process.env.STORYBOOK_DEV === 'true'

export const Question = ({
  language,
  question: { attributes = {} },
  question,
  handleRemove,
  handleDuplicate,
  update,
  questionNumber,
  groupIndex,
  questionIndex,
  lastQuestionIndex,
  questionGroupIsOpen,
  handleSwapQuestionPosition,
  isTestMode = false,
  surveySettings,
}) => {
  const questionRef = useRef(null)
  const questionBodyRef = useRef(null)
  const [, isInView] = useIsInViewport(questionRef)
  const [virtualHeight, setVirtualHeight] = useState(0)

  const { focused = {}, setFocused } = useFocused()
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const { getError } = useErrors()
  const [isTitleFocused, setIsTitleFocused] = useState(false)
  const questionImageObject = useMemo(
    () => getQuestionImageObjectFromImageAttribute(attributes?.image),
    [attributes?.image]
  )

  const isQuestionDisabled = useMemo(() => {
    const filteredThemeName = Object.values(getQuestionTypeInfo()).map(
      (q) => q.theme
    )

    return (
      (getDisabledQuestionTypes().includes(question.questionThemeName) ||
        !filteredThemeName.includes(question.questionThemeName)) &&
      !isInTestMode
    )
  }, [question.questionThemeName])

  const [, setHasErrors] = useState(false)

  const handleFocusQuestion = () => {
    const questionIsNotFocused = question.qid !== focused.qid
    if (questionIsNotFocused) {
      setFocused(question, groupIndex, questionIndex)
    }
  }

  useEffect(() => {
    if (isTitleFocused && focused.qid !== question.qid) {
      setFocused(question, groupIndex, questionIndex)
    }
  }, [isTitleFocused])

  const handleOnErrors = (errors) => setHasErrors(errors)

  useEffect(() => {
    const questionIsFocused =
      focused.type === question.type && question.title === focused.title

    if (!questionIsFocused) {
      return
    }

    if (questionGroupIsOpen) {
      ScrollToElement(questionRef.current)
    }
  }, [focused.qid, question.qid, question.sortOrder])

  const handleUpdate = (change) => {
    setFocused({ ...question, ...change }, groupIndex, questionIndex, false)
    update({ ...question, ...change })
  }

  if (!question?.qid) {
    return <></>
  }

  const isFocused = useMemo(
    () => focused.qid === question.qid,
    [focused.qid, question.qid]
  )

  useEffect(() => {
    if (!isInView) {
      return
    }

    if (questionBodyRef.current) {
      setVirtualHeight(questionBodyRef.current.clientHeight)
    }
  }, [])

  return (
    <TooltipContainer
      tip={getTooltipMessages().NO_PERMISSION}
      showTip={!hasSurveyUpdatePermission}
      placement="left"
    >
      <div
        data-error={getError(question.qid, Entities.question)}
        onClick={handleFocusQuestion}
        id={`${question.qid}-question`}
        data-testid={`question`}
        className={classNames(
          'question position-relative',
          getAttributeValue(attributes.cssclass),
          {
            'focus-element': isFocused,
            'hover-element': !isFocused,
            'opacity-25': isTrue(getAttributeValue(attributes.hide_question)),
            'cursor-not-allowed': !hasSurveyUpdatePermission,
            'p-0': questionImageObject.hasQuestionImageAsBackground,
          }
        )}
        ref={questionRef}
      >
        {isInView || isInTestMode ? (
          <QuestionContainer
            questionImageObject={questionImageObject}
            update={(image) => {
              // Update the image attribute in the question
              const updatedAttributes = {
                ...question.attributes,
                ...image,
              }
              handleUpdate({ attributes: updatedAttributes })
            }}
            qid={question.qid}
          >
            <div
              className="w-100"
              data-testid="question-container"
              ref={questionBodyRef}
            >
              <div>
                <QuestionHeader
                  handleUpdate={handleUpdate}
                  language={language}
                  question={question}
                  questionNumber={questionNumber}
                  onError={(errors) => handleOnErrors(errors)}
                  isFocused={focused.qid === question.qid}
                  setIsTitleFocused={setIsTitleFocused}
                />
              </div>
              <div className="question-body-container">
                <QuestionBody
                  language={language}
                  question={question}
                  handleUpdate={handleUpdate}
                  questionNumber={questionNumber}
                  isFocused={focused.qid === question.qid}
                  isQuestionDisabled={isQuestionDisabled}
                  surveySettings={surveySettings}
                  isTitleFocused={isTitleFocused}
                />
              </div>
              <div>
                {hasSurveyUpdatePermission && (
                  <div>
                    <QuestionFooter
                      question={question}
                      isFocused={focused.qid === question.qid}
                      handleUpdate={handleUpdate}
                      handleRemove={handleRemove}
                      handleDuplicate={handleDuplicate}
                    />
                  </div>
                )}
              </div>
            </div>
          </QuestionContainer>
        ) : (
          <QuestionSkeleton height={virtualHeight} />
        )}
        {!isTestMode && isFocused && (
          <div className="position-absolute question-scroll">
            <div>
              <Button
                variant="secondary"
                onClick={() => handleSwapQuestionPosition(-1)}
                size="sm"
                disabled={questionIndex === 0}
                className="question-scroll-button"
                data-testid="question-arrow-up-button"
              >
                <ArrowUpIcon className="text-white fill-current" />
              </Button>
            </div>
            <div className="mt-1">
              <Button
                onClick={() => handleSwapQuestionPosition(+1)}
                variant="secondary"
                size="sm"
                disabled={questionIndex === lastQuestionIndex}
                className="question-scroll-button"
                data-testid="question-arrow-down-button"
              >
                <ArrowDownIcon className="text-white fill-current" />
              </Button>
            </div>
          </div>
        )}
      </div>
    </TooltipContainer>
  )
}
