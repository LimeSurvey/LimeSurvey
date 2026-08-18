import classNames from 'classnames'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'

export const ProjectTitleBadge = ({
  projectTitle,
  canEdit,
  onClick,
  showBadge = false,
}) => {
  if (!projectTitle) return null

  return (
    <TooltipContainer tip={showBadge && projectTitle} placement="bottom">
      <div
        className={classNames('project-title-badge', {
          'disable-settings': !canEdit,
          'opacity-0 pointer-events-none': !showBadge,
        })}
        onClick={canEdit ? onClick : undefined}
        aria-label={showBadge && `${t('Project title')}: ${projectTitle}`}
        disabled={!canEdit}
      >
        {showBadge && projectTitle}
      </div>
    </TooltipContainer>
  )
}
