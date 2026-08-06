import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'

export const ProjectTitleBadge = ({ projectTitle, canEdit, onClick }) => {
  if (!projectTitle) return null

  return (
    <TooltipContainer tip={projectTitle} placement="bottom">
      <button
        className="project-title-badge"
        type="button"
        onClick={canEdit ? onClick : undefined}
        aria-label={`${t('Project title')}: ${projectTitle}`}
        disabled={!canEdit}
        style={!canEdit ? { pointerEvents: 'none' } : undefined}
      >
        <span className="project-title-badge__text">{projectTitle}</span>
      </button>
    </TooltipContainer>
  )
}
