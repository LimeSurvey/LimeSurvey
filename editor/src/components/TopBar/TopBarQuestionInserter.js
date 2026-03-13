import React from 'react'

import { QuestionTypeSelector } from 'components/QuestionTypeSelector'
import { getQuestionTypeInfo } from 'components/QuestionTypes'
import {
  RandomNumber,
  SCALE_2,
  STATES,
  NEW_OBJECT_ID_PREFIX,
  createBufferOperation,
  getAnswerExample,
  getNextAnswerCode,
  getQuestionExample,
  getNextQuestionCode,
  getNextSubQuestionCode,
} from 'helpers'
import { useAppState, useBuffer, useFocused, useSurvey } from 'hooks'

export const TopBarQuestionInserter = ({ surveyID }) => {
  const [, setIsAddingQuestionOrGroup] = useAppState(
    STATES.IS_ADDING_QUESTION_OR_GROUP,
    false
  )
  const [codeToQuestion] = useAppState(STATES.CODE_TO_QUESTION, {})

  const [clickedQuestionGroupIndex, setClickedQuestionGroupIndex] = useAppState(
    STATES.CLICKED_QUESTION_GROUP_INDEX,
    null
  )
  const { survey, update, language } = useSurvey(surveyID)
  const { addToBuffer } = useBuffer()
  const { setFocused, groupIndex } = useFocused()
  const questionGroupToAddQuestion = clickedQuestionGroupIndex
    ? survey?.questionGroups[clickedQuestionGroupIndex]
    : groupIndex
      ? survey?.questionGroups[groupIndex]
      : survey?.questionGroups[0]

  const handleAddQuestionGroup = (newQuestionGroup, index) => {
    if (!survey.questionGroups) {
      survey.questionGroups = []
    }

    const newQuestionGroupIndex = index
      ? index + 1
      : survey.questionGroups.length

    const updatedQuestionGroups = [
      ...survey.questionGroups.slice(0, newQuestionGroupIndex),
      newQuestionGroup,
      ...survey.questionGroups.slice(newQuestionGroupIndex),
    ].map((questionGroup, index) => {
      questionGroup.sortOrder = index + 1
      return questionGroup
    })

    survey.questionGroups = updatedQuestionGroups

    const operation = createBufferOperation(newQuestionGroup.gid)
      .questionGroup()
      .create({
        questionGroup: {
          ...newQuestionGroup,
          sortOrder: 1,
          gRelevance: '',
          sid: surveyID,
          tempId: newQuestionGroup.gid,
        },
        questionGroupL10n: newQuestionGroup.l10ns,
      })

    update({ ...survey })
    addToBuffer(operation)
    setFocused(newQuestionGroup, newQuestionGroupIndex)
  }

  const addQuestionGroup = () => {
    const groupId = RandomNumber()
    const newQuestionGroup = {
      gid: NEW_OBJECT_ID_PREFIX + groupId,
      sid: surveyID,
      type: getQuestionTypeInfo().QUESTION_GROUP.type,
      theme: getQuestionTypeInfo().QUESTION_GROUP.theme,
      l10ns: {
        [language]: {
          groupName: '',
          description: '',
        },
      },
      questions: [],
    }

    handleAddQuestionGroup(newQuestionGroup)
    setIsAddingQuestionOrGroup(false)
  }

  const handleAddQuestion = (question) => {
    const _groupIndex = clickedQuestionGroupIndex
      ? clickedQuestionGroupIndex
      : groupIndex
        ? groupIndex
        : 0

    const updatedQuestionGroups = [...survey.questionGroups]
    updatedQuestionGroups[_groupIndex] = {
      ...updatedQuestionGroups[_groupIndex],
      questions: [...updatedQuestionGroups[_groupIndex].questions, question],
    }

    survey.questionGroups = updatedQuestionGroups
    update({ ...survey })

    setFocused(
      question,
      _groupIndex,
      updatedQuestionGroups[_groupIndex].questions.length - 1
    )

    setClickedQuestionGroupIndex(null)

    const operation = createBufferOperation(question.qid)
      .question()
      .create({
        question: { ...question, tempId: question.qid },
        questionL10n: { ...question.l10ns },
        attributes: { ...question.attributes },
        answers: { ...question.answers },
        subquestions: { ...question.subquestions },
      })
    addToBuffer(operation)
  }

  const addNewQuestion = (questionTypeInfo) => {
    const questionId = `${NEW_OBJECT_ID_PREFIX}${RandomNumber()}`
    const questionThemeName = questionTypeInfo.questionThemeName

    if (
      (!questionGroupToAddQuestion &&
        questionTypeInfo.type !== getQuestionTypeInfo().QUESTION_GROUP.type) ||
      !questionTypeInfo.type
    ) {
      return
    } else if (
      questionTypeInfo.type === getQuestionTypeInfo().QUESTION_GROUP.type
    ) {
      addQuestionGroup()
      return
    }

    const questionWithAnswersTheme = [
      getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO.theme,
      getQuestionTypeInfo().SINGLE_CHOICE_LIST_RADIO_WITH_COMMENT.theme,
      getQuestionTypeInfo().SINGLE_CHOICE_BUTTONS.theme,
      getQuestionTypeInfo().SINGLE_CHOICE_DROPDOWN.theme,
      getQuestionTypeInfo().SINGLE_CHOICE_IMAGE_SELECT.theme,
      getQuestionTypeInfo().ARRAY.theme,
      getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme,
      getQuestionTypeInfo().ARRAY_COLUMN.theme,
      getQuestionTypeInfo().RANKING.theme,
      getQuestionTypeInfo().RANKING_ADVANCED.theme,
    ]

    const questionWithSubquestionsTheme = [
      getQuestionTypeInfo().ARRAY_TEXT.theme,
      getQuestionTypeInfo().ARRAY.theme,
      getQuestionTypeInfo().ARRAY_NUMBERS.theme,
      getQuestionTypeInfo().ARRAY_COLUMN.theme,
      getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme,
      getQuestionTypeInfo().MULTIPLE_CHOICE.theme,
      getQuestionTypeInfo().MULTIPLE_CHOICE_WITH_COMMENTS.theme,
      getQuestionTypeInfo().MULTIPLE_SHORT_TEXTS.theme,
      getQuestionTypeInfo().MULTIPLE_NUMERICAL_INPUTS.theme,
      getQuestionTypeInfo().MULTIPLE_CHOICE_BUTTONS.theme,
      getQuestionTypeInfo().MULTIPLE_CHOICE_IMAGE_SELECT.theme,
    ]

    const questionWithAnswersDualScale2Theme = [
      getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme,
    ]

    const questionWithSubquestionsDualScale2Theme = [
      getQuestionTypeInfo().ARRAY_TEXT.theme,
      getQuestionTypeInfo().ARRAY_NUMBERS.theme,
    ]

    let answers = []
    let subQuestions = []

    if (questionWithAnswersTheme.includes(questionThemeName)) {
      answers.push(
        getAnswerExample({
          qid: questionId,
          code: getNextAnswerCode(codeToQuestion, questionId, 0),
          language,
          sortOrder: 1,
          languages: survey.languages,
        }),
        getAnswerExample({
          qid: questionId,
          code: getNextAnswerCode(codeToQuestion, questionId, 1),
          sortOrder: 2,
          languages: survey.languages,
        })
      )
    }

    if (questionWithSubquestionsTheme.includes(questionThemeName)) {
      subQuestions.push(
        getQuestionExample({
          sortOrder: 1,
          parentQid: questionId,
          type: getQuestionTypeInfo().LONG_TEXT.type,
          title: getNextSubQuestionCode(null, null, 0),
          questionThemeName: getQuestionTypeInfo().LONG_TEXT.theme,
          gid: questionGroupToAddQuestion.gid,
          sid: questionGroupToAddQuestion.sid,
          languages: survey.languages,
        }),
        getQuestionExample({
          sortOrder: 2,
          languages: survey.languages,
          parentQid: questionId,
          type: getQuestionTypeInfo().LONG_TEXT.type,
          title: getNextSubQuestionCode(null, null, 1),
          questionThemeName: getQuestionTypeInfo().LONG_TEXT.theme,
          gid: questionGroupToAddQuestion.gid,
          sid: questionGroupToAddQuestion.sid,
        })
      )
    }

    if (questionWithAnswersDualScale2Theme.includes(questionThemeName)) {
      answers.push(
        getAnswerExample({
          qid: questionId,
          code: getNextAnswerCode(codeToQuestion, questionId, 2),
          languages: survey.languages,
          sortOrder: 3,
          scaleId: SCALE_2,
        }),
        getAnswerExample({
          qid: questionId,
          code: getNextAnswerCode(codeToQuestion, questionId, 3),
          languages: survey.languages,
          sortOrder: 4,
          scaleId: SCALE_2,
        })
      )
    }

    if (questionWithSubquestionsDualScale2Theme.includes(questionThemeName)) {
      subQuestions.push(
        getQuestionExample({
          sortOrder: 3,
          languages: survey.languages,
          parentQid: questionId,
          type: getQuestionTypeInfo().LONG_TEXT.type,
          title: getNextSubQuestionCode(null, null, 2),
          questionThemeName: getQuestionTypeInfo().LONG_TEXT.theme,
          gid: questionGroupToAddQuestion.gid,
          sid: questionGroupToAddQuestion.sid,
          scaleId: SCALE_2,
        }),
        getQuestionExample({
          sortOrder: 4,
          languages: survey.languages,
          parentQid: questionId,
          type: getQuestionTypeInfo().LONG_TEXT.type,
          title: getNextSubQuestionCode(null, null, 3),
          questionThemeName: getQuestionTypeInfo().LONG_TEXT.theme,
          gid: questionGroupToAddQuestion.gid,
          sid: questionGroupToAddQuestion.sid,
          scaleId: SCALE_2,
        })
      )
    }

    const newQuestion = getQuestionExample({
      qid: questionId,
      gid: questionGroupToAddQuestion.gid,
      sid: questionGroupToAddQuestion.sid,
      type: questionTypeInfo.type,
      questionThemeName: questionThemeName,
      title: getNextQuestionCode(codeToQuestion),
      answers: answers,
      subquestions: subQuestions,
      sortOrder: questionGroupToAddQuestion.questions.length + 1,
      attributes: getDefaultAttributes(questionThemeName),
      languages: survey.languages,
    })

    handleAddQuestion(newQuestion)
    setIsAddingQuestionOrGroup(false)
  }

  const getDefaultAttributes = (questionThemeName) => {
    if (questionThemeName === getQuestionTypeInfo().BROWSER_DETECTION.theme) {
      return { location_mapservice: { '': '100' } }
    }

    return {}
  }

  return (
    <div
      data-testid="topbar-question-inserter"
      id="topbar-question-inserter"
      className="mt-4 question-type-selector-container topbar-question-inserter"
    >
      <QuestionTypeSelector
        disableAddingQuestions={!questionGroupToAddQuestion}
        callBack={addNewQuestion}
      />
    </div>
  )
}
