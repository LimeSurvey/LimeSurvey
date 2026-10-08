import classNames from 'classnames'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'

export const SurveyCodeBadge = ({
  surveyCode,
  canEdit,
  onClick,
  showBadge = false,
}) => {
  if (!surveyCode) return null

  return (
    <TooltipContainer tip={showBadge && surveyCode} placement="bottom">
      <div
        className={classNames('internal-title-badge', {
          'disable-settings': !canEdit,
          'opacity-0 pointer-events-none': !showBadge,
        })}
        onClick={canEdit ? onClick : undefined}
        aria-label={showBadge && `${t('Internal title')}: ${surveyCode}`}
        disabled={!canEdit}
      >
        {showBadge && surveyCode}
      </div>
    </TooltipContainer>
  )
}
