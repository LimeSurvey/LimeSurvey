import { useEffect, useRef, useState } from 'react'
import classNames from 'classnames'

import { Section } from 'components/Survey/Section'
import { Card } from 'components/Survey/Card'
import { FOCUS_ANIMATION_DURATION_IN_MS, ScrollToElement } from 'helpers'
import { useFocused } from 'hooks'

import { QuestionGroupHeader } from './QuestionGroupHeader'
import { QuestionGroupFooter } from './QuestionGroupFooter'
import { QuestionGroupBody } from './QuestionGroupBody'

const QuestionGroup = ({
  language,
  defaultLanguage,
  questionGroup = {},
  groupIndex,
  addQuestionGroup,
  update,
  duplicateGroup,
  deleteGroup,
  firstQuestionNumber,
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
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [focused, questionGroup.gid])

  useEffect(() => {
    const isQuestionGroupFocused = focused.gid === questionGroup.gid
    const notFocusingAQuestion = !questionIndex

    if (isQuestionGroupFocused && notFocusingAQuestion) {
      ScrollToElement(questionGroupTitleRef.current)
    }

    if (questionGroup.tempFocusTitle) {
      setTimeout(() => {
        delete questionGroup.tempFocusTitle
        update({ ...questionGroup })
      }, 1000)
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
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

  const handleAddQuestion = (question) => {
    handleUpdateQuestions([...questionGroup.questions, question])
    setFocused(question, groupIndex, questionGroup.questions?.length)
  }

  const handleAddQuestionGroup = (questionGroup) => {
    setHighlightQuestionGroup(false)
    addQuestionGroup(questionGroup)
  }

  const onToggleAddQuestionOverlay = (state) => {
    setHighlightQuestionGroup(state)
  }

  return (
    <Section className="question-group">
      <QuestionGroupHeader
        deleteGroup={deleteGroup}
        duplicateGroup={duplicateGroup}
        groupIndex={groupIndex}
        handleUpdate={(groupName) => handleUpdateL10ns(groupName)}
        language={language}
        questionGroup={questionGroup}
        questionsLength={questionGroup.questions?.length}
        questionGroupTitleRef={questionGroupTitleRef}
        handleFocusGroup={handleFocusGroup}
        setShowQuestions={setShowQuestions}
        showQuestions={showQuestions}
        onErrors={setErrors}
      />
      <Card
        className={classNames('question-group-editor-area', {
          'focus-element':
            highlightQuestionGroup &&
            !errors &&
            questionGroup.questions?.length,
        })}
      >
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
        />
      </Card>
      <QuestionGroupFooter
        defaultLanguage={defaultLanguage}
        language={language}
        questionGroup={questionGroup}
        handleFocusGroup={handleFocusGroup}
        handleAddQuestion={handleAddQuestion}
        handleAddQuestionGroup={handleAddQuestionGroup}
        onToggleAddQuestionOverlay={onToggleAddQuestionOverlay}
      />
    </Section>
  )
}

export default QuestionGroup
