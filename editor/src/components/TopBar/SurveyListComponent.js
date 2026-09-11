import { Form } from 'react-bootstrap'
import classNames from 'classnames'
import { L10ns, RemoveHTMLTagsInString } from 'helpers'

export const SurveyListComponent = ({
  surveyId,
  surveyList,
  activeLanguage,
  surveyTitleIsFocused,
  surveyTitleWidth,
  titleRef,
  titleSelectOffset,
  handleSurveySwitch,
}) => {
  return (
    <Form.Select
      aria-label="Default select example"
      onChange={handleSurveySwitch}
      value={surveyId}
      className={classNames('form-select-top-bar', {
        'd-none': surveyTitleIsFocused,
      })}
      style={{
        width: surveyTitleWidth + titleSelectOffset,
        height: titleRef.current?.offsetHeight,
      }}
    >
      {surveyList.map((survey) => {
        let title = L10ns({
          prop: 'title',
          language: activeLanguage,
          l10ns: survey.languageSettings,
        })
        title = title
          ? title
          : L10ns({
              prop: 'title',
              language: survey.language,
              l10ns: survey.languageSettings,
            })
        return (
          <option key={survey.sid} value={survey.sid}>
            {RemoveHTMLTagsInString(title)}
          </option>
        )
      })}
    </Form.Select>
  )
}
