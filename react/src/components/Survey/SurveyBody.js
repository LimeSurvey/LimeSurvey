import { useFocused } from 'hooks'
import { AddQuestion } from './AddQuestion'
import { QuestionGroups } from './QuestionGroups'

export const SurveyBody = ({
  questionGroups,
  defaultLanguage,
  language,
  update,
  surveyId,
}) => {
  const { setFocused } = useFocused()

  const handleAddingFirstQuestionGroup = (questionGroup) => {
    update({ questionGroups: [questionGroup] })
    setFocused(questionGroup, 0)
  }

  // If there are no question groups, show the Add Question button.
  return (
    <>
      {questionGroups?.length ? (
        <QuestionGroups
          language={language}
          defaultLanguage={defaultLanguage}
          questionGroups={questionGroups}
          update={(questionGroups) => update({ questionGroups })}
        />
      ) : (
        <div
          className={'add-question d-flex justify-content-center mt-4'}
          style={{ color: '#14ae5c', zIndex: 3 }}
        >
          <AddQuestion
            toggleDarkOnOpen={false}
            handleAddQuestionGroup={handleAddingFirstQuestionGroup}
            surveyId={surveyId}
          />
        </div>
      )}
    </>
  )
}
