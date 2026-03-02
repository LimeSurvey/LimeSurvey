import { AddQuestion } from './AddQuestion'
import { QuestionGroups } from './QuestionGroups'

export const SurveyBody = ({
  questionGroups,
  language,
  update,
  surveySettings,
}) => {
  // If there are no question groups, show the Add Question button.
  return (
    <>
      {questionGroups?.length ? (
        <QuestionGroups
          language={language}
          questionGroups={questionGroups}
          update={(questionGroups) => update({ questionGroups })}
          surveySettings={surveySettings}
        />
      ) : (
        <div className="d-flex align-items-center justify-content-center">
          <div className={'add-question d-flex justify-content-center mt-4'}>
            <AddQuestion />
          </div>
        </div>
      )}
    </>
  )
}
