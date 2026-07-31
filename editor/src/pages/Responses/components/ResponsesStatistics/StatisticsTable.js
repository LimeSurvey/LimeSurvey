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
    const statTerm = (label, tip) => (
      <span className="responses-statistics-stat-term">
        {label}{' '}
        <TooltipContainer tip={tip}>
          <i className="ri-question-line"></i>
        </TooltipContainer>
      </span>
    )
    const minTerm = statTerm(
      t('Min'),
      <>
        <strong>{t('Minimum')}</strong>{' '}
        {t(
          'shows the lowest value given by participants in the collected data.'
        )}
      </>
    )
    const maxTerm = statTerm(
      t('Max'),
      <>
        <strong>{t('Maximum')}</strong>{' '}
        {t(
          'shows the highest value given by participants in the collected data.'
        )}
      </>
    )
    // One column per calculation, single row with the values; min/max share
    // a combined column.
    const columns = [
      { key: 'count', title: t('Count'), value: stats.count },
      { key: 'sum', title: t('Sum'), value: stats.sum },
      {
        key: 'standardDeviation',
        title: statTerm(
          t('Standard deviation'),
          <>
            <strong>{t('Standard deviation')}</strong>{' '}
            {t('shows how much the values vary from the average.')}
            <br />
            {t(
              'A low value means responses are similar, while a high value indicates greater differences between participants.'
            )}
          </>
        ),
        value: stats.standardDeviation,
      },
      {
        key: 'mean',
        title: statTerm(
          t('Average'),
          <>
            <strong>{t('Arithmetic mean')}</strong>{' '}
            {t('shows the average value of all responses.')}
            <br />
            {t(
              'It is calculated by adding all values together and dividing the result by the number of responses.'
            )}
          </>
        ),
        value: stats.mean,
      },
      {
        key: 'minMax',
        title: (
          <>
            {minTerm} - {maxTerm}
          </>
        ),
        value:
          stats.min !== undefined && stats.max !== undefined
            ? `${stats.min} - ${stats.max}`
            : undefined,
      },
      {
        key: 'median',
        title: statTerm(
          t('2nd quartile (median)'),
          <>
            <strong>{t('Median')}</strong>{' '}
            {t('shows the middle value of all responses.')}
            <br />
            {t(
              'Half of the responses are below this value, and the other half are above it.'
            )}
          </>
        ),
        value: stats.median,
      },
    ]
      .filter((column) => column.value !== undefined)
      .map(({ key, title, value }) => ({ key, title, render: () => value }))
    return (
      <LSTable
        columns={columns}
        data={[{ id: 'stats' }]}
        rowId="id"
        resizable
      />
    )
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
