import { Button } from 'react-bootstrap'
import { Dash } from 'react-bootstrap-icons'
import Select from 'react-select'
import classNames from 'classnames'

export const Option = ({
  allQuestionOptions,
  allOperatorsOptions,
  handleSelection,
  option,
  index,
  removeOption,
}) => {
  return (
    <>
      <div className="d-flex gap-2">
        <div style={{ width: '520px' }} className="flex-1">
          {index % 2 === 0 ? (
            <div className="d-flex  gap-2">
              <Select
                value={
                  option?.index !== undefined
                    ? allQuestionOptions[option.index]
                    : ''
                }
                placeholder="Choose a question"
                options={allQuestionOptions}
                onChange={(e) => handleSelection(e, index)}
                className={classNames('flex-1 w-100', {
                  'error-focus': option.hasError,
                })}
                styles={{
                  option: (baseStyles) => ({
                    ...baseStyles,
                    textAlign: 'left',
                  }),
                }}
              />
              <Button
                onClick={removeOption}
                style={{ color: '#fff' }}
                variant={'danger'}
              >
                <Dash />
              </Button>
            </div>
          ) : (
            <>
              <Select
                value={
                  option?.index !== undefined
                    ? allOperatorsOptions[option.index]
                    : ''
                }
                onChange={(e) => handleSelection(e, index)}
                placeholder="Choose an operator"
                options={allOperatorsOptions}
                className={classNames({
                  'error-focus': option.hasError,
                })}
              />
            </>
          )}
        </div>
      </div>
    </>
  )
}
