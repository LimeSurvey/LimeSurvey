import { Button } from 'components'
import { SwalAlert } from 'helpers'
import { format } from 'util'

export const DeleteScenarioOverlay = ({ scenarioId, onConfirmDelete }) => {
  return (
    <div className="condition-designer-overlay">
      <div className="condition-designer-overlay-title">
        <p>{t('Confirm')}</p>
      </div>

      <p className="condition-designer-overlay-message">
        {format(
          t('Are you sure you want to delete scenario %s for this question?'),
          scenarioId
        )}
      </p>

      <div className="condition-designer-overlay-actions">
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
