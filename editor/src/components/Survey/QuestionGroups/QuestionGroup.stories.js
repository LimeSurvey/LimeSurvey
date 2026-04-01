import QuestionGroupComponent from './QuestionGroup'

export default {
  title: 'General/QuestionGroup',
  component: QuestionGroupComponent,
}

export const QuestionGroup = ({ survey, update }) => {
  return (
    <QuestionGroupComponent
      questionGroup={survey?.questionGroups[0]}
      update={update}
      firstQuestionNumber={0}
      groupIndex={0}
      deleteGroup={() => {}}
      duplicateGroup={() => {}}
      language="en"
      surveySettings={{ showNoAnswer: true }}
    />
  )
}
