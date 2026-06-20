import { LSTable } from 'components'

import {
  VALUE_TYPE,
  getDisplayMetric,
  ordinal,
  shouldRenderImage,
} from './ChartsUtils'

// Answer cell content (colour swatch + image or text) shared by the tables.
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

const withRowIds = (rows) =>
  rows.map((row, index) => ({ ...row, id: row.key ?? `row-${index}` }))

export const StatisticsTable = ({ data = [], isImage = false }) => {
  const isRanking = data.some((item) => Array.isArray(item?.ranks))
  const isSegmented = data.some((item) => Array.isArray(item?.segments))

  if (isRanking) {
    // Option × rank-position matrix, options sorted by total.
    const rows = withRowIds(
      [...data].sort((a, b) => (b.value ?? 0) - (a.value ?? 0))
    )
    const columns = [
      {
        key: 'answer',
        title: '',
        render: (row) => <AnswerCell row={row} isImage={isImage} />,
      },
      ...(rows[0]?.ranks ?? []).map((rank) => ({
        key: `rank-${rank.position}`,
        title: ordinal(rank.position),
        render: (row) =>
          row.ranks?.find((r) => r.position === rank.position)?.value ?? '',
      })),
    ]
    return <LSTable columns={columns} data={rows} rowId="id" resizable />
  }

  if (isSegmented) {
    // Array: subquestion rows × scale-point/answer columns, cells are the
    // per-segment counts.
    const columns = [
      { key: 'subquestion', title: '', render: (row) => row.title },
      ...(data[0]?.segments ?? []).map((segment) => ({
        key: segment.key,
        title: segment.title,
        render: (row) =>
          row.segments?.find((s) => s.key === segment.key)?.value ?? '',
      })),
    ]
    return (
      <LSTable columns={columns} data={withRowIds(data)} rowId="id" resizable />
    )
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

  return <LSTable columns={columns} data={withRowIds(data)} rowId="id" resizable />
}
