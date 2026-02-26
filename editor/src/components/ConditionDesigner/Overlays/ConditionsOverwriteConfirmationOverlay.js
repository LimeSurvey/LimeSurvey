import { Button } from 'components/UIComponents'
import { SwalAlert } from 'helpers'

export const ConditionOverwriteConfirmation = ({
  onConfirmChanges,
  onCancelChanges = () => {},
}) => {
  return (
    <div className="condition-designer-overlay">
      <div className="condition-designer-overlay-title">
        <p>{t('Overwriting condition from condition builder')}</p>
      </div>

      <p className="condition-designer-overlay-message">
        {t(
          'Your condition will no longer be editable in the visual builder. Are you sure you want to apply these changes?'
        )}
      </p>

      <div className="condition-designer-overlay-actions">
        <Button
          variant="secondary"
          className="condition-designer-overlay-secondary-button"
          onClick={() => {
            SwalAlert.close()
            onCancelChanges()
          }}
        >
          {t('Cancel')}
        </Button>
        <Button
          variant="primary"
          className="condition-designer-overlay-primary-button"
          onClick={() => {
            SwalAlert.close()
            onConfirmChanges()
          }}
        >
          {t('Save changes')}
        </Button>
      </div>
    </div>
  )
}
