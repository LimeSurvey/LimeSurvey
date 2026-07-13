import { Table } from 'react-bootstrap'

import { LSTable } from 'components'

import {
  VALUE_TYPE,
  getDisplayMetric,
  ordinal,
  shouldRenderImage,
} from './ChartsUtils'

// Answer cell content (colour swatch + image or text) shared by both tables.
const AnswerCell = ({ row, isImage }) => (
  <>
    <span
      className="responses-statistics-table-swatch"
      style={{ backgroundColor: row.fill }}
    />
    {shouldRenderImage(isImage, row) ? (
      <img
        src={row.title}
        alt={row.title}
        className="responses-statistics-table-image"
      />
    ) : (
      row.title
    )}
  </>
)

// Ranking renders as an option × rank-position matrix with a pinned first
// column and horizontal scroll, which the shared LSTable doesn't support, so it
// keeps its bespoke table.
const RankingTable = ({ rows, isImage }) => {
  const valueColumns = (rows[0]?.ranks ?? []).map((rank) => ({
    key: `rank-${rank.position}`,
    header: ordinal(rank.position),
    render: (row) =>
      row.ranks?.find((r) => r.position === rank.position)?.value ?? '',
  }))

  return (
    <div className="responses-statistics-table-wrap">
      <Table className="table responses-statistics-table responses-statistics-table--ranking">
        <thead>
          <tr>
            <th className="responses-statistics-table-answer"></th>
            {valueColumns.map((column) => (
              <th key={column.key}>{column.header}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {rows.map((row, index) => (
            <tr key={`responses-statistics-table-row-${index}`}>
              <td className="responses-statistics-table-answer">
                <AnswerCell row={row} isImage={isImage} />
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

export const StatisticsTable = ({ data = [], isImage = false }) => {
  const isRanking = data.some((item) => Array.isArray(item?.ranks))

  if (isRanking) {
    const rows = [...data].sort((a, b) => (b.value ?? 0) - (a.value ?? 0))
    return <RankingTable rows={rows} isImage={isImage} />
  }

  const columns = [
    {
      key: 'answer',
      title: t('Answer'),
      render: (row) => <AnswerCell row={row} isImage={isImage} />,
    },
    {
      key: 'responses',
      title: t('Responses'),
      render: (row) => getDisplayMetric(row, VALUE_TYPE.COUNT),
    },
    {
      key: 'percentage',
      title: t('Percentage'),
      render: (row) => getDisplayMetric(row, VALUE_TYPE.PERCENTAGE),
    },
  ]

  const rows = data.map((row, index) => ({
    ...row,
    id: row.key ?? `row-${index}`,
  }))

  return <LSTable columns={columns} data={rows} rowId="id" />
}
