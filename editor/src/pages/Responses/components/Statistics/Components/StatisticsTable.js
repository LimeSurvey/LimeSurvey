import { Table } from 'react-bootstrap'

import {
  VALUE_TYPE,
  getDisplayMetric,
  shouldRenderImage,
} from '../../ResponsesStatistics/ChartsUtils'

export const StatisticsTable = ({
  data = [],
  valueType = VALUE_TYPE.PERCENTAGE,
  isImage = false,
}) => {
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE

  return (
    <Table className="table statistics-table">
      <thead>
        <tr>
          <th>{t('Answer')}</th>
          <th>{isPercentage ? t('Percentage') : t('Responses')}</th>
        </tr>
      </thead>
      <tbody>
        {data.map((item, index) => (
          <tr key={`statistics-table-row-${index}`}>
            <td>
              <span
                className="statistics-table-swatch"
                style={{ backgroundColor: item.fill }}
              />
              {shouldRenderImage(isImage, item) ? (
                <img
                  src={item.title}
                  alt={item.title}
                  className="statistics-table-image"
                />
              ) : (
                item.title
              )}
            </td>
            <td>{getDisplayMetric(item, valueType)}</td>
          </tr>
        ))}
      </tbody>
    </Table>
  )
}
