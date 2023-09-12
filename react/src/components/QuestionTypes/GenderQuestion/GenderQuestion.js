import { Circle, GenderMale } from 'react-bootstrap-icons'
import { FormCheck } from 'react-bootstrap'

import { IsTrue } from 'helpers'
import { ToggleButtons } from 'components/UIComponents'

export const GenderQuestion = ({ question, handleUpdate }) => {
  const options = [
    { icon: GenderMale, name: 'Male', value: 'male' },
    { icon: GenderMale, name: 'Female', value: 'female' },
  ]

  if (!IsTrue(question.mandatory)) {
    options.push({
      icon: Circle,
      name: 'No answer',
      value: 'no answer',
    })
  }

  return (
    <div className="gender" style={{ maxWidth: 400 }}>
      {question.displayType !== 'radio' && (
        <ToggleButtons
          onChange={(answerExample) => handleUpdate({ answerExample })}
          value={question.answerExample}
          toggleOptions={options}
          height={'30px'}
          id={`${question.qid}-gender-question`}
        />
      )}
      {question.displayType === 'radio' && (
        <div className="d-flex align-items-center gap-3">
          <FormCheck
            value={'male'}
            type={'radio'}
            label={'Male'}
            name={`${question.qid}-gender-question-radio-list`}
            data-testid="single-choice-radio-answer"
            checked={question.answerExample === 'male'}
            onChange={() => handleUpdate({ answerExample: 'male' })}
          />
          <FormCheck
            value={'female'}
            type={'radio'}
            label={'Female'}
            name={`${question.qid}-gender-question-radio-list`}
            data-testid="single-choice-radio-answer"
            checked={question.answerExample === 'female'}
            onChange={() => handleUpdate({ answerExample: 'female' })}
          />
          {!IsTrue(question.mandatory) && (
            <FormCheck
              value={'no answer'}
              type={'radio'}
              label={'No answer'}
              name={`${question.qid}-gender-question-radio-list`}
              data-testid="single-choice-radio-answer"
              checked={question.answerExample === 'no answer'}
              onChange={() => handleUpdate({ answerExample: 'no answer' })}
            />
          )}
        </div>
      )}
    </div>
  )
}
