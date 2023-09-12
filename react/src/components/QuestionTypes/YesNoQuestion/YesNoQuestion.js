import { FormCheck } from 'react-bootstrap'
import { Check2, X, Circle } from 'react-bootstrap-icons'

import { IsTrue } from 'helpers'
import { ToggleButtons } from 'components/UIComponents'

export const YesNoQuestion = ({ question, handleUpdate }) => {
  const options = [
    { icon: Check2, name: 'Yes', value: 'yes' },
    { icon: X, name: 'No', value: 'no' },
  ]

  if (!IsTrue(question.mandatory)) {
    options.push({
      icon: Circle,
      name: 'No answer',
      value: 'no answer',
    })
  }

  return (
    <div className="yes-no-question" style={{ maxWidth: 400 }}>
      {question.displayType !== 'radio' && (
        <ToggleButtons
          onChange={(answerExample) => handleUpdate({ answerExample })}
          value={question.answerExample}
          toggleOptions={options}
          height={'30px'}
          id={`yes-no-question-${question.qid}-toggle-buttons`}
        />
      )}
      {question.displayType === 'radio' && (
        <div className="d-flex align-items-center gap-3">
          <FormCheck
            value={'yes'}
            type={'radio'}
            label={'Yes'}
            name={`${question.qid}-yes-no-question-radio-list`}
            checked={question.answerExample === 'yes'}
            onChange={() => handleUpdate({ answerExample: 'yes' })}
          />
          <FormCheck
            value={'no'}
            type={'radio'}
            label={'No'}
            name={`${question.qid}-yes-no-question-radio-list`}
            checked={question.answerExample === 'no'}
            onChange={() => handleUpdate({ answerExample: 'no' })}
          />
          {!IsTrue(question.mandatory) && (
            <FormCheck
              value={'no answer'}
              type={'radio'}
              label={'No answer'}
              name={`${question.qid}-yes-no-question-radio-list`}
              checked={question.answerExample === 'no answer'}
              onChange={() => handleUpdate({ answerExample: 'no answer' })}
            />
          )}
        </div>
      )}
    </div>
  )
}
