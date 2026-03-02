import classNames from 'classnames'

import { Button } from 'components/UIComponents'

export const TutorialTooltip = ({
  backProps,
  index,
  size,
  primaryProps,
  step,
  tooltipProps,
}) => {
  return (
    <div className="tutorial-tooltip" {...tooltipProps}>
      <div className="content-container">
        <div className="semi18">{step.title}</div>
        <div className="reg14">{step.content}</div>
      </div>
      <div className="d-flex w-100 gap-3 justify-content-between align-items-center">
        <Button
          disabled={index < 1}
          className="p-0 border-none mt-1"
          variant="none"
          {...backProps}
        >
          <i className="ri-arrow-left-line"></i>
        </Button>
        <div className="progress-bar-container">
          <div
            style={{ width: `${(index / size) * 100}%` }}
            className="progress-bar"
          ></div>
        </div>
        <Button
          className={classNames('p-0 mt-1 med12', {
            'border-none': index < size - 1,
            'end-tour': index === size - 1,
          })}
          {...primaryProps}
          variant="none"
          disabled={step.disableNextButton}
        >
          {index < size - 1 ? (
            <i className="ri-arrow-right-line"></i>
          ) : (
            'End tour'
          )}
        </Button>
      </div>
    </div>
  )
}
