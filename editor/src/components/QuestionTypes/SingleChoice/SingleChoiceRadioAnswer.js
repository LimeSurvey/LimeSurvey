import { FormCheck } from 'react-bootstrap'

import { ContentEditor } from 'components/UIComponents'

export const SingleChoiceRadioAnswer = ({
  answer: { code } = {},
  qid,
  handleFocus = () => {},
  handleBlur = () => {},
  value = '',
  handleUpdateAnswer = () => {},
}) => {
  return (
    <FormCheck
      onClick={(e) => {
        e.stopPropagation()
      }}
      value={code}
      type={'radio'}
      className="choice"
      label={
        <ContentEditor
          onFocus={handleFocus}
          onBlur={handleBlur}
          placeholder={t('Answer option')}
          disabled={true}
          className="single-choice-form-label choice"
          value={value}
          update={handleUpdateAnswer}
        />
      }
      name={`${qid}-radio-list`}
      data-testid="single-choice-radio-answer"
    />
  )
}
