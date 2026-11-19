import classNames from 'classnames'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'

export const SurveyCodeBadge = ({
  surveyCode,
  canEdit,
  onClick,
  showBadge = false,
}) => {
  if (!surveyCode) return null

  // Not a native button: a disabled one gets no mouse events, so the tooltip with
  // the full (possibly cut off) code wouldn't show for users who can't edit it
  const isEditable = canEdit && showBadge

  const onKeyDown = (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault()
      onClick()
    }
  }

  return (
    <TooltipContainer tip={showBadge ? surveyCode : ''} placement="bottom">
      <div
        className={classNames('internal-title-badge', {
          'disable-settings': !canEdit,
          'opacity-0 pointer-events-none': !showBadge,
        })}
        role={showBadge ? 'button' : undefined}
        tabIndex={isEditable ? 0 : undefined}
        aria-disabled={showBadge && !canEdit ? true : undefined}
        aria-label={
          showBadge ? `${t('Internal title')}: ${surveyCode}` : undefined
        }
        onClick={isEditable ? onClick : undefined}
        onKeyDown={isEditable ? onKeyDown : undefined}
        data-testid="internal-title-badge"
      >
        {showBadge && surveyCode}
      </div>
    </TooltipContainer>
  )
}
