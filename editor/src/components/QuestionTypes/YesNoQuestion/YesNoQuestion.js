import { FormCheck } from 'react-bootstrap'
import CheckboxCircleLineIcon from 'remixicon-react/CheckboxCircleLineIcon'
import CloseCircleLineIcon from 'remixicon-react/CloseCircleLineIcon'
import CheckboxBlankCircleLineIcon from 'remixicon-react/CheckboxBlankCircleLineIcon'

import { getAttributeValue, getNoAnswerLabel, isTrue } from 'helpers'
import { getDisplayButtonTypeOptions } from 'helpers/options'
import { Button } from 'components/UIComponents'
import { useEffect, useState } from 'react'
import classNames from 'classnames'

export const YesNoQuestion = ({
  question,
  surveySettings,
  values = [],
  onValueChange = () => {},
}) => {
  const valueInfo = values?.[0] || {}
  const [currentSelectedOptionIndex, setCurrentSelectedOptionIndex] =
    useState(2)

  const options = [
    { icon: CheckboxCircleLineIcon, name: st('Yes'), value: 'Y' },
    { icon: CloseCircleLineIcon, name: st('No'), value: 'N' },
  ]

  useEffect(() => {
    const info = valueInfo.value

    options.map((option, index) => {
      if (info === option.value) {
        setCurrentSelectedOptionIndex(index)
      }
    })
  }, [values])

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

  const handleValueChange = (value, key, index) => {
    onValueChange(value, key)
    setCurrentSelectedOptionIndex(index)
  }

  return (
    <div
      className="yes-no-question d-flex align-items-center gap-3"
      style={{ maxWidth: 400 }}
      data-testid="yes-no-question"
    >
      {displayType === getDisplayButtonTypeOptions().RADIO.value && (
        <>
          <FormCheck
            value={'Y'}
            type={'radio'}
            label={st('Yes')}
            name={`${question.qid}-yes-no-question-radio-list`}
            data-testid="yes-no-question-answer"
            defaultChecked={currentSelectedOptionIndex === 0}
            onClick={() => handleValueChange('Y', valueInfo.key, 0)}
          />
          <FormCheck
            value={'N'}
            type={'radio'}
            label={st('No')}
            name={`${question.qid}-yes-no-question-radio-list`}
            data-testid="yes-no-question-answer"
            defaultChecked={currentSelectedOptionIndex === 1}
            onClick={() => handleValueChange('N', valueInfo.key, 1)}
          />
          {!isTrue(question.mandatory) && (
            <FormCheck
              value={null}
              type={'radio'}
              label={getNoAnswerLabel(true)}
              name={`${question.qid}-yes-no-question-radio-list`}
              data-testid="yes-no-question-answer"
              defaultChecked={currentSelectedOptionIndex === 2}
              onClick={() => handleValueChange(null, valueInfo.key, 2)}
            />
          )}
        </>
      )}
      {displayType !== getDisplayButtonTypeOptions().RADIO.value && (
        <>
          {options.map((option, idx) => (
            <Button
              className={classNames(`button-question yes-no-question-button`, {
                'btn-success text-light border-success':
                  currentSelectedOptionIndex === idx,
              })}
              key={`yes-no-question-button-${idx}`}
              id={`${question.qid}-yes-no-question`}
              Icon={option.icon}
              name={option.name}
              text={option.name}
              testId="yes-no-question-answer"
              value={option.value}
              iconSize={32}
              variant="outline-success"
              onClick={() =>
                handleValueChange(option.value, valueInfo.key, idx)
              }
            />
          ))}
        </>
      )}
    </div>
  )
}
