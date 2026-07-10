import { LSTable, TooltipContainer } from 'components'

import {
  VALUE_TYPE,
  getDisplayMetric,
  getUnionSegments,
  ordinal,
  shouldRenderImage,
} from './ChartsUtils'

const AnswerCell = ({ row, isImage }) =>
  shouldRenderImage(isImage, row) ? (
    <img
      src={row.title}
      alt={row.title}
      className="responses-statistics-table-image"
    />
  ) : (
    row.title
  )

const withRowIds = (rows) =>
  rows.map((row, index) => ({ ...row, id: row.key ?? `row-${index}` }))

export const StatisticsTable = ({ data = [], isImage = false }) => {
  const isRanking = data.some((item) => Array.isArray(item?.ranks))
  const isSegmented = data.some((item) => Array.isArray(item?.segments))
  const statsItem = data.find((item) => item?.stats)

  if (statsItem) {
    const { stats } = statsItem
    const rows = [
      { id: 'count', title: t('Count'), value: stats.count },
      { id: 'sum', title: t('Sum'), value: stats.sum },
      {
        id: 'standardDeviation',
        title: t('Standard deviation'),
        value: stats.standardDeviation,
        tip: (
          <>
            <strong>{t('Standard deviation')}</strong>{' '}
            {t('shows how much the values vary from the average.')}
            <br />
            {t(
              'A low value means responses are similar, while a high value indicates greater differences between participants.'
            )}
          </>
        ),
      },
      {
        id: 'mean',
        title: t('Average'),
        value: stats.mean,
        tip: (
          <>
            <strong>{t('Arithmetic mean')}</strong>{' '}
            {t('shows the average value of all responses.')}
            <br />
            {t(
              'It is calculated by adding all values together and dividing the result by the number of responses.'
            )}
          </>
        ),
      },
      {
        id: 'min',
        title: t('Minimum'),
        value: stats.min,
        tip: (
          <>
            <strong>{t('Minimum')}</strong>{' '}
            {t(
              'shows the lowest value given by participants in the collected data.'
            )}
          </>
        ),
      },
      {
        id: 'max',
        title: t('Maximum'),
        value: stats.max,
        tip: (
          <>
            <strong>{t('Maximum')}</strong>{' '}
            {t(
              'shows the highest value given by participants in the collected data.'
            )}
          </>
        ),
      },
      {
        id: 'median',
        title: t('2nd quartile (median)'),
        value: stats.median,
        tip: (
          <>
            <strong>{t('Median')}</strong>{' '}
            {t('shows the middle value of all responses.')}
            <br />
            {t(
              'Half of the responses are below this value, and the other half are above it.'
            )}
          </>
        ),
      },
    ].filter((row) => row.value !== undefined)
    const columns = [
      {
        key: 'calculation',
        title: t('Calculation'),
        render: (row) =>
          row.tip ? (
            <span className="responses-statistics-stat-term">
              {row.title}{' '}
              <TooltipContainer tip={row.tip}>
                <i className="ri-question-line"></i>
              </TooltipContainer>
            </span>
          ) : (
            row.title
          ),
      },
      { key: 'result', title: t('Result'), render: (row) => row.value },
    ]
    return <LSTable columns={columns} data={rows} rowId="id" resizable />
  }

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
    const columns = [
      {
        key: 'subquestion',
        title: '',
        render: (row) =>
          row.scaleTitle ? `${row.title} — ${row.scaleTitle}` : row.title,
      },
      ...getUnionSegments(data).map((segment, index) => ({
        key: `segment-${index}`,
        title: segment.title,
        render: (row) =>
          row.segments?.find((s) => s.title === segment.title)?.value ?? '',
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

  return (
    <LSTable columns={columns} data={withRowIds(data)} rowId="id" resizable />
  )
}
