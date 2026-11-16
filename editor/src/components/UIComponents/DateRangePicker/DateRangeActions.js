import { Button } from '../Buttons'

export const DateRangeActions = ({
  onClear,
  onCancel,
  onConfirm,
  confirmDisabled = false,
}) => (
  <div className="action-buttons">
    <Button
      variant=""
      className="date-range-btn date-range-btn-clear"
      onClick={onClear}
    >
      {t('Clear')}
    </Button>
    <div className="action-buttons-end">
      <Button
        variant=""
        className="date-range-btn date-range-btn-cancel"
        onClick={onCancel}
      >
        {t('Cancel')}
      </Button>
      <Button
        variant=""
        className="date-range-btn date-range-btn-confirm"
        onClick={onConfirm}
        disabled={confirmDisabled}
      >
        {t('Confirm')}
      </Button>
    </div>
  </div>
)
