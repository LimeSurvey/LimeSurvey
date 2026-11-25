import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'
import { Badge } from 'components/UIComponents'
import { useQuestionTypes } from 'hooks'

const resolveThemeTitle = (questionTypes, type, themeName, fallback) => {
  if (!themeName) return fallback
  const questionType = questionTypes[themeName]
  return questionType?.type === type ? questionType.title : fallback
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
  const { questionTypes } = useQuestionTypes()
  const themeTitle = resolveThemeTitle(
    questionTypes,
    type,
    themeName,
    typeLabel
  )

  return (
    <div className="responses-statistics-chart-header">
      <div className="responses-statistics-chart-title">
        <div className="responses-statistics-chart-title-main">
          <span className="responses-statistics-chart-title-index">
            {index + 1} <i className="ri-arrow-right-line" />
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
              actionsTitle={t('Chart actions')}
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
