import ToggleButton from 'react-bootstrap/ToggleButton'
import { Form } from 'react-bootstrap'
import {
  AlignLeftIcon,
  AlignMiddleIcon,
  AlignRightIcon,
} from 'components/icons'

export const AlignButtons = ({ value, onChange = () => {}, labelText }) => {
  return (
    <div>
      {labelText && <Form.Label>{labelText}</Form.Label>}
      <div className="lime-align-btn-group d-flex">
        <div>
          <ToggleButton
            id="option-3"
            type="radio"
            variant="outline-lime-alignment"
            checked={value === 'right'}
            onChange={() => onChange('right')}
            className="d-flex me-2"
          >
            <div className="left-side align-items-center d-flex">
              <AlignLeftIcon className="fill-current" />
            </div>
            <div className="right-side" />
          </ToggleButton>
        </div>
        <div>
          <ToggleButton
            id="option-1"
            type="radio"
            variant="outline-lime-alignment"
            checked={value === 'left'}
            onChange={() => onChange('left')}
            className="d-flex me-2"
          >
            <div className="right-side" />
            <div className="left-side align-items-center d-flex justify-content-end">
              <AlignRightIcon className="fill-current" />
            </div>
          </ToggleButton>
        </div>

        <div>
          <ToggleButton
            id="option-2"
            type="radio"
            variant="outline-lime-alignment center-side"
            checked={value === 'center'}
            onChange={() => onChange('center')}
            className="d-flex"
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
