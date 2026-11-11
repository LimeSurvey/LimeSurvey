import { Button } from 'components/UIComponents'

/**
 * Shown instead of a label set list when the list request failed.
 */
export const LabelSetListError = ({ onRetry = () => {}, testId }) => (
  <div className="label-set-modal-error" role="alert" data-testid={testId}>
    <span>{t('The label sets could not be loaded.')}</span>
    <Button
      variant="link"
      className="p-0 label-set-modal-retry"
      onClick={() => onRetry()}
    >
      {t('Try again')}
    </Button>
  </div>
)
