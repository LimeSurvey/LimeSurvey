import { Button } from 'components'
import { SwalAlert } from 'helpers'

export const UnsavedChangesOverlay = ({ onIgnoreChanges, message = false }) => {
  const defaultMessage = t(
    'You are about to go back without saving your changes on this scenario. Do you want to proceed?'
  )
  return (
    <div className="condition-designer-overlay">
      <div className="condition-designer-overlay-title">
        <p>{t('Unsaved changes')}</p>
      </div>

      <p className="condition-designer-overlay-message">
        {message ? message : defaultMessage}
      </p>

      <div className="condition-designer-overlay-actions">
        <Button
          variant="secondary"
          className="condition-designer-overlay-secondary-button"
          onClick={() => {
            onIgnoreChanges()
            SwalAlert.close()
          }}
        >
          {t('Go back without saving')}
        </Button>
        <Button
          variant="primary"
          className="condition-designer-overlay-primary-button"
          onClick={() => SwalAlert.close()}
        >
          {t('Continue editing')}
        </Button>
      </div>
    </div>
  )
}
