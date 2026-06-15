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
    <div className="chart-header">
      <div className="chart-title">
        <div className="chart-title-main">
          <span className="chart-title-index">
            {index} <i className="ri-arrow-right-line" />
          </span>
          <span className="chart-title-key">{code}</span>
          <Badge>{themeTitle}</Badge>
          <span className="chart-title-text">{title}</span>
        </div>
        {actions.length > 0 && (
          <div className="chart-title-actions">
            <MeatballMenu
              items={actions}
              shouldDisableIfSurveyActive={false}
              meatballClassName="chart-header-meatball-menu"
              actionsTitle="Chart Actions"
              placement="bottom-end"
            />
          </div>
        )}
      </div>
      <p className="chart-title-help">{help}</p>
    </div>
  )
}
