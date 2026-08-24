import classNames from 'classnames'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'

export const InternalTitleBadge = ({
  internalTitle,
  canEdit,
  onClick,
  showBadge = false,
}) => {
  if (!internalTitle) return null

  return (
    <TooltipContainer tip={showBadge && internalTitle} placement="bottom">
      <div
        className={classNames('internal-title-badge', {
          'disable-settings': !canEdit,
          'opacity-0 pointer-events-none': !showBadge,
        })}
        onClick={canEdit ? onClick : undefined}
        aria-label={showBadge && `${t('Internal title')}: ${internalTitle}`}
        disabled={!canEdit}
      >
        {showBadge && internalTitle}
      </div>
    </TooltipContainer>
  )
}
