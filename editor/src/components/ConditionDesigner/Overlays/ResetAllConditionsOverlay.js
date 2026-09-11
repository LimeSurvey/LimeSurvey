import { Button } from 'components'
import { SwalAlert } from 'helpers'

export const ResetAllConditionsOverlay = ({ onConfirmDelete }) => {
  return (
    <div className="condition-designer-overlay">
      <div className="condition-designer-overlay-title reg24">
        <p>{t('Delete condition')}</p>
      </div>

      <p className="condition-designer-overlay-message reg14">
        {t('Are you sure you want to delete all conditions for this question?')}
      </p>

      <div className="condition-designer-overlay-actions d-flex justify-content-end">
        <Button
          variant="secondary"
          className="condition-designer-overlay-secondary-button"
          onClick={() => SwalAlert.close()}
        >
          {t('Cancel')}
        </Button>
        <Button
          variant="danger"
          className="condition-designer-overlay-danger-button"
          onClick={() => {
            SwalAlert.close()
            onConfirmDelete()
          }}
        >
          {t('Delete')}
        </Button>
      </div>
    </div>
  )
}
