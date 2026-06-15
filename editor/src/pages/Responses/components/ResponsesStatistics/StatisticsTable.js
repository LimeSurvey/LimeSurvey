import { Table } from 'react-bootstrap'

import {
  VALUE_TYPE,
  getDisplayMetric,
  ordinal,
  shouldRenderImage,
} from './ChartsUtils'

export const StatisticsTable = ({ data = [], isImage = false }) => {
  const isRanking = data.some((item) => Array.isArray(item?.ranks))

  const rows = isRanking
    ? [...data].sort((a, b) => (b.value ?? 0) - (a.value ?? 0))
    : data

  const valueColumns = isRanking
    ? (rows[0]?.ranks ?? []).map((rank) => ({
        key: `rank-${rank.position}`,
        header: ordinal(rank.position),
        render: (row) =>
          row.ranks?.find((r) => r.position === rank.position)?.value ?? '',
      }))
    : [
        {
          key: 'responses',
          header: t('Responses'),
          render: (row) => getDisplayMetric(row, VALUE_TYPE.COUNT),
        },
        {
          key: 'percentage',
          header: t('Percentage'),
          render: (row) => getDisplayMetric(row, VALUE_TYPE.PERCENTAGE),
        },
      ]

  return (
    <div className="statistics-table-wrap">
      <Table
        className={`table statistics-table${
          isRanking ? ' statistics-table--ranking' : ''
        }`}
      >
        <thead>
          <tr>
            <th className="statistics-table-answer">
              {isRanking ? '' : t('Answer')}
            </th>
            {valueColumns.map((column) => (
              <th key={column.key}>{column.header}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((row, index) => (
            <tr key={`statistics-table-row-${index}`}>
              <td className="statistics-table-answer">
                <span
                  className="statistics-table-swatch"
                  style={{ backgroundColor: row.fill }}
                />
                {shouldRenderImage(isImage, row) ? (
                  <img
                    src={row.title}
                    alt={row.title}
                    className="statistics-table-image"
                  />
                ) : (
                  row.title
                )}
              </td>
              {valueColumns.map((column) => (
                <td key={column.key}>{column.render(row)}</td>
              ))}
            </tr>
          ))}
        </tbody>
      </Table>
    </div>
  )
}
