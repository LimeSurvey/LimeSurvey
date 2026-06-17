import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'
import { Badge } from 'components/UIComponents'
import { getQuestionTypeInfo } from 'components/QuestionTypes'

const resolveThemeTitle = (type, themeName, fallback) => {
  if (!themeName) return fallback
  const info = Object.values(getQuestionTypeInfo()).find(
    (entry) => entry.type === type && entry.theme === themeName
  )
  return info?.title ?? fallback
}

export const ChartHeader = ({
  index,
  code,
  type,
  themeName,
  typeLabel,
  title,
  help,
  actions = [],
}) => {
  const themeTitle = resolveThemeTitle(type, themeName, typeLabel)

  return (
    <div className="responses-statistics-chart-header">
      <div className="responses-statistics-chart-title">
        <div className="responses-statistics-chart-title-main">
          <span className="responses-statistics-chart-title-index">
            {index} <i className="ri-arrow-right-line" />
          </span>
          <span className="responses-statistics-chart-title-key">{code}</span>
          <Badge>{themeTitle}</Badge>
          <span className="responses-statistics-chart-title-text">{title}</span>
        </div>
        {actions.length > 0 && (
          <div className="responses-statistics-chart-title-actions">
            <MeatballMenu
              items={actions}
              shouldDisableIfSurveyActive={false}
              meatballClassName="responses-statistics-chart-menu"
              actionsTitle="Chart Actions"
              placement="bottom-end"
              submenuPlacement="left"
            />
          </div>
        )}
      </div>
      <p className="responses-statistics-chart-title-help">{help}</p>
    </div>
  )
}
