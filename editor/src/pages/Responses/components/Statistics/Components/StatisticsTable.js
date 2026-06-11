import { Table } from 'react-bootstrap'

import { VALUE_TYPE } from '../../ResponsesStatistics/ChartsUtils'

export const StatisticsTable = ({
  data = [],
  valueType = VALUE_TYPE.PERCENTAGE,
  isImage = false,
}) => {
  const total = data.reduce((sum, item) => sum + (Number(item.value) || 0), 0)
  const isPercentage = valueType === VALUE_TYPE.PERCENTAGE

  return (
    <Table className="table statistics-table">
      <thead>
        <tr>
          <th>{t('Answer')}</th>
          <th className="text-end">
            {isPercentage ? t('Percentage') : t('Responses')}
          </th>
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
              {isImage ? (
                <img
                  src={item.title}
                  alt={item.title}
                  className="statistics-table-image"
                />
              ) : (
                item.title
              )}
            </td>
            <td className="text-end">
              {isPercentage ? `${item.percentage}%` : item.value}
            </td>
          </tr>
        ))}
      </tbody>
      <tfoot>
        <tr>
          <td>{t('Total')}</td>
          <td className="text-end">{isPercentage ? '100%' : total}</td>
        </tr>
      </tfoot>
    </Table>
  )
}
