import { FormCheck } from 'react-bootstrap'
import WomenLineIcon from 'remixicon-react/WomenLineIcon'
import MenLineIcon from 'remixicon-react/MenLineIcon'
import CheckboxBlankCircleLineIcon from 'remixicon-react/CheckboxBlankCircleLineIcon'
import { useEffect, useState } from 'react'
import classNames from 'classnames'

import { getAttributeValue, isTrue, getNoAnswerLabel } from 'helpers'
import { getDisplayButtonTypeOptions } from 'helpers/options'
import { Button } from 'components/UIComponents'

export const GenderQuestion = ({
  question,
  surveySettings,
  values = [],
  onValueChange = () => {},
}) => {
  const valueInfo = values?.[0] || {}
  const [currentSelectedOptionIndex, setCurrentSelectedOptionIndex] =
    useState(2)

  const options = [
    { icon: WomenLineIcon, name: st('Female'), value: 'F' },
    { icon: MenLineIcon, name: st('Male'), value: 'M' },
  ]

  useEffect(() => {
    const info = valueInfo.value

    options.map((option, index) => {
      if (info === option.value) {
        setCurrentSelectedOptionIndex(index)
      }
    })
  }, [values])

  const handleValueChange = (value, key, index) => {
    onValueChange(value, key)
    setCurrentSelectedOptionIndex(index)
  }

  const displayType = getAttributeValue(question.attributes.display_type)
  const showNoAnswer =
    !isTrue(question.mandatory) && surveySettings.showNoAnswer

  if (showNoAnswer) {
    options.push({
      icon: CheckboxBlankCircleLineIcon,
      name: getNoAnswerLabel(true),
      value: null,
    })
  }

  return (
    <div
      className="gender-question d-flex align-items-center gap-3"
      style={{ maxWidth: 400 }}
      data-testid="gender-question"
    >
      {displayType === getDisplayButtonTypeOptions().RADIO.value && (
        <div className="d-flex align-items-center gap-3">
          <FormCheck
            value={'F'}
            type={'radio'}
            label={st('Female')}
            name={`${question.qid}-gender-question-radio-list`}
            data-testid="gender-question-option"
            defaultChecked={currentSelectedOptionIndex === 0}
            onClick={() => handleValueChange('F', valueInfo.key, 0)}
          />
          <FormCheck
            value={'M'}
            type={'radio'}
            label={st('Male')}
            name={`${question.qid}-gender-question-radio-list`}
            data-testid="gender-question-option"
            defaultChecked={currentSelectedOptionIndex === 1}
            onClick={() => handleValueChange('M', valueInfo.key, 1)}
          />
          {!isTrue(question.mandatory) && (
            <FormCheck
              value={'no answer'}
              type={'radio'}
              label={getNoAnswerLabel(true)}
              name={`${question.qid}-gender-question-radio-list`}
              data-testid="gender-question-option"
              defaultChecked={currentSelectedOptionIndex === 2}
              onClick={() => handleValueChange(null, valueInfo.key, 2)}
            />
          )}
        </div>
      )}
      <div className="gender-buttons d-flex align-items-center gap-3">
        {displayType !== getDisplayButtonTypeOptions().RADIO.value && (
          <>
            {options.map((option, idx) => (
              <Button
                className={classNames(
                  `button-question gender-question-button`,
                  {
                    'btn-success text-light border-success':
                      currentSelectedOptionIndex === idx,
                  }
                )}
                key={`gender-question-button-${idx}`}
                id={`${question.qid}-gender-question`}
                Icon={option.icon}
                name={option.name}
                text={option.name}
                iconSize={32}
                variant="outline-success"
                testId="gender-question-option"
                active={currentSelectedOptionIndex === idx}
                onClick={() =>
                  handleValueChange(option.value, valueInfo.key, idx)
                }
              />
            ))}
          </>
        )}
      </div>
    </div>
  )
}
