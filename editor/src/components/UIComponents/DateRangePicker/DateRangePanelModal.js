import { Modal } from 'react-bootstrap'

import { DateRangePanel } from './DateRangePanel'

/**
 * DateRangePanel in a modal of its own, without a header. The panel props are
 * passed through, and Cancel, Escape or a click outside close it.
 */
export const DateRangePanelModal = ({ show, onHide, label, ...panelProps }) => (
  <Modal
    show={show}
    onHide={onHide}
    centered
    className="date-range-panel-modal"
    aria-label={label}
  >
    <div className="date-range-picker">
      <DateRangePanel onCancel={onHide} {...panelProps} />
    </div>
  </Modal>
)
