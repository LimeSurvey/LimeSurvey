import classNames from 'classnames'
import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'

export const ProjectTitleBadge = ({ projectTitle, canEdit, onClick }) => {
  if (!projectTitle) return null

  return (
    <TooltipContainer tip={projectTitle} placement="bottom">
      <div
        className={classNames('project-title-badge', {
          'disable-settings': !canEdit,
        })}
        onClick={canEdit ? onClick : undefined}
        aria-label={`${t('Project title')}: ${projectTitle}`}
        disabled={!canEdit}
      >
        {projectTitle}
      </div>
    </TooltipContainer>
  )
}
