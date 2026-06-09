import { useEffect, useMemo, useRef, useState } from 'react'
import classNames from 'classnames'

import { Section } from 'components/Survey/Section'
import { Card } from 'components/Survey/Card'
import { FOCUS_ANIMATION_DURATION_IN_MS, ScrollToElement } from 'helpers'
import { useFocused } from 'hooks'

import { QuestionGroupHeader } from './QuestionGroupHeader'
import { QuestionGroupFooter } from './QuestionGroupFooter'
import { QuestionGroupBody } from './QuestionGroupBody'

export const QuestionGroup = ({
  language = 'en',
  questionGroup = {},
  groupIndex,
  update = () => {},
  duplicateGroup = () => {},
  deleteGroup = () => {},
  firstQuestionNumber,
  surveySettings,
}) => {
  const [showQuestions, setShowQuestions] = useState(true)
  const [, setFocusDescription] = useState(false)
  const [highlightQuestionGroup, setHighlightQuestionGroup] = useState(false)
  const [errors, setErrors] = useState('')

  const questionGroupTitleRef = useRef(null)

  const { focused = {}, setFocused, questionIndex } = useFocused()

  useEffect(() => {
    const isQuestionGroupFocused = focused.gid === questionGroup.gid
    const isFocusingAQuestion = focused.qid

    if (isQuestionGroupFocused && isFocusingAQuestion) {
      setShowQuestions(true)
    }

    if (!isQuestionGroupFocused || isFocusingAQuestion) {
      return
    }

    setHighlightQuestionGroup(true)

    setTimeout(() => {
      setHighlightQuestionGroup(false)
    }, FOCUS_ANIMATION_DURATION_IN_MS)
  }, [focused, questionGroup.gid])

  useEffect(() => {
    const isQuestionGroupFocused = focused.gid === questionGroup.gid
    const notFocusingAQuestion = questionIndex === undefined

    if (isQuestionGroupFocused && notFocusingAQuestion) {
      ScrollToElement(questionGroupTitleRef.current)
    }
  }, [focused, questionGroup.gid, questionIndex])

  const handleUpdate = (change) => {
    update({
      ...questionGroup,
      ...change,
    })
  }

  const handleUpdateL10ns = (updatedL10ns) => {
    const updateL10ns = {
      ...questionGroup.l10ns,
    }

    updateL10ns[language] = {
      ...updateL10ns[language],
      ...updatedL10ns,
    }

    handleUpdate({ l10ns: updateL10ns })
  }

  const handleUpdateQuestions = (questions) => {
    setHighlightQuestionGroup(false)
    handleUpdate({ questions })
  }

  const handleFocusGroup = () => {
    if (focused.gid !== questionGroup.gid || focused.qid) {
      setFocused(questionGroup, groupIndex)
    }
  }

  const isFocused = useMemo(
    () => focused.gid === questionGroup.gid && !questionIndex,
    [questionGroup.gid]
  )

  return (
    <Section
      testId="question-group"
      className={classNames('question-group', {
        'focus-element':
          highlightQuestionGroup && !errors && questionGroup.questions?.length,
      })}
    >
      <QuestionGroupHeader
        deleteGroup={deleteGroup}
        duplicateGroup={duplicateGroup}
        groupIndex={groupIndex}
        handleUpdate={(groupName) => handleUpdateL10ns(groupName)}
        language={language}
        questionGroup={questionGroup}
        questionsLength={questionGroup.questions?.length}
        handleFocusGroup={handleFocusGroup}
        setShowQuestions={setShowQuestions}
        showQuestions={showQuestions}
        onErrors={setErrors}
        isFocused={isFocused}
      />
      <Card className="question-group-editor-area">
        <QuestionGroupBody
          firstQuestionNumber={firstQuestionNumber}
          focused={focused}
          setFocused={setFocused}
          handleUpdateQuestions={handleUpdateQuestions}
          handleFocusGroup={handleFocusGroup}
          language={language}
          questionGroup={questionGroup}
          questionIndex={questionIndex}
          setFocusDescription={setFocusDescription}
          showQuestions={showQuestions}
          handleUpdate={(description) => handleUpdateL10ns(description)}
          groupIndex={groupIndex}
          surveySettings={surveySettings}
        />
      </Card>
      <QuestionGroupFooter groupIndex={groupIndex} />
    </Section>
  )
}

export default QuestionGroup
