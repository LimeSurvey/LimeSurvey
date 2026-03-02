import ToggleButton from 'react-bootstrap/ToggleButton'
import { Form } from 'react-bootstrap'
import { AlignLeftIcon } from 'components/icons/AlignLeftIcon'
import { AlignMiddleIcon } from 'components/icons/AlignMiddleIcon'
import { AlignRightIcon } from 'components/icons/AlignRightIcon'

export const AlignButtons = ({ value, update, labelText }) => {
  return (
    <div>
      {labelText && (
        <Form.Label data-testid="align-buttons-label-text">
          {labelText}
        </Form.Label>
      )}
      <div className="lime-align-btn-group d-flex">
        <div data-testid="option-3">
          <ToggleButton
            id="option-3"
            type="radio"
            variant="outline-lime-alignment"
            checked={value === 'right'}
            onChange={() => update('right')}
            className="d-flex me-2"
            data-testid="right-align-btn"
          >
            <div className="left-side align-items-center d-flex">
              <AlignLeftIcon className="fill-current" />
            </div>
            <div className="right-side" />
          </ToggleButton>
        </div>
        <div data-testid="option-1">
          <ToggleButton
            id="option-1"
            type="radio"
            variant="outline-lime-alignment"
            checked={value === 'left'}
            onChange={() => update('left')}
            className="d-flex me-2"
            data-testid="left-align-btn"
          >
            <div className="right-side" />
            <div className="left-side align-items-center d-flex justify-content-end">
              <AlignRightIcon className="fill-current" />
            </div>
          </ToggleButton>
        </div>
        <div data-testid="option-2">
          <ToggleButton
            id="option-2"
            type="radio"
            variant="outline-lime-alignment center-side"
            checked={value === 'center'}
            onChange={() => update('center')}
            className="d-flex"
            data-testid="center-align-btn"
          >
            <div className="center-side align-items-center d-flex justify-content-center">
              <AlignMiddleIcon className="icon fill-current" />
            </div>
          </ToggleButton>
        </div>
      </div>
    </div>
  )
}
