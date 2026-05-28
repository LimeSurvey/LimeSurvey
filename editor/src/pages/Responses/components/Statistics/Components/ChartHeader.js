import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'
import { Badge } from 'components/UIComponents'

export const ChartHeader = ({
  index,
  code,
  type,
  title,
  help,
  actions = [],
}) => {
  return (
    <div className="chart-header">
      <div className="chart-title">
        <div className="chart-title-main">
          <span className="chart-title-index">
            {index} <i className="ri-arrow-right-line" />
          </span>
          <span className="chart-title-key">{code}</span>
          <Badge>{type}</Badge>
          <span className="chart-title-text">{title}</span>
        </div>
        {actions.length > 0 && (
          <div className="chart-title-actions">
            <MeatballMenu
              items={actions}
              shouldDisableIfSurveyActive={false}
              meatballClassName="chart-header-meatball-menu"
              placement="bottom-end"
            />
          </div>
        )}
      </div>
      {help && <p className="chart-title-help">{help}</p>}
    </div>
  )
}
