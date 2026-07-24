import React, { memo, useMemo } from 'react'

import { useAppState, useSurvey } from 'hooks'
import { STATES } from 'helpers'

import { getDataWithPercentages } from './ChartsUtils.js'
import { ChartRendererV2 } from './ChartRenderV2.js'

const UNGROUPED_KEY = '__ungrouped__'

const getQuestionGroupId = (statisticsItem) =>
  statisticsItem?.meta?.question?.gid ??
  statisticsItem?.meta?.question?.groupId ??
  null

const getGroupTitle = (group, activeLanguage) => {
  if (!group) return null
  const localized =
    group.l10ns?.[activeLanguage] ??
    group.l10ns?.[Object.keys(group.l10ns || {})[0]]
  return localized?.groupName ?? group.groupName ?? null
}

const StatisticsChartCard = memo(function StatisticsChartCard({
  item,
  index,
  surveyId,
  valueType,
  filters,
}) {
  const data = useMemo(() => getDataWithPercentages(item), [item])
  const question = useMemo(
    () => ({
      type: item?.meta?.question?.type,
      typeLabel: item?.meta?.question?.typeLabel,
      themeName: item?.meta?.question?.themeName,
      code: item?.meta?.question?.code,
      title: item?.title,
      help: item?.meta?.question?.help,
      fields: item?.meta?.question?.fields,
      index: index,
    }),
    [item, index]
  )
  const chartId =
    item?.meta?.question?.qid ?? item?.meta?.question?.id ?? item?.title

  return (
    <ChartRendererV2
      index={index}
      surveyId={surveyId}
      chartId={chartId}
      data={data}
      valueType={valueType}
      filters={filters}
      question={question}
    />
  )
})

// Charts render in API order, which follows the survey structure (groups in
// survey order, questions in group order), so paginated pages append
// sequentially.
const renderCharts = (items, surveyId, valueType, filters) => (
  <div className="responses-statistics-charts row">
    {items.map(({ item, index }) => (
      <div className="col-12" key={`responses-statistics-charts-${index}`}>
        <StatisticsChartCard
          item={item}
          index={index}
          surveyId={surveyId}
          valueType={valueType}
          filters={filters}
        />
      </div>
    ))}
  </div>
)

export const StatisticsContainer = ({
  statistics,
  surveyId,
  valueType,
  filters,
}) => {
  const { survey } = useSurvey(surveyId)
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)

  const grouped = useMemo(() => {
    const questionGroups = survey?.questionGroups || []
    const groupOrder = questionGroups.map((group) => Number(group.gid))
    const groupOrderSet = new Set(groupOrder)
    const groupTitles = questionGroups.reduce((acc, group) => {
      acc[Number(group.gid)] = getGroupTitle(group, activeLanguage)
      return acc
    }, {})

    const buckets = {}
    statistics.forEach((item, index) => {
      const gid = getQuestionGroupId(item)
      const key = gid != null ? Number(gid) : UNGROUPED_KEY
      if (!buckets[key]) buckets[key] = []
      buckets[key].push({ item, index })
    })

    const orderedKeys = [
      ...groupOrder.filter((key) => buckets[key]),
      ...Object.keys(buckets)
        .filter((key) => key !== UNGROUPED_KEY)
        .map(Number)
        .filter((key) => !groupOrderSet.has(key)),
      ...(buckets[UNGROUPED_KEY] ? [UNGROUPED_KEY] : []),
    ]

    return orderedKeys.map((key) => ({
      key,
      title: key === UNGROUPED_KEY ? null : (groupTitles[key] ?? null),
      items: buckets[key],
    }))
  }, [statistics, survey?.questionGroups, activeLanguage])

  return (
    <div className="responses-statistics-body">
      {grouped.map((group) => (
        <div className="responses-statistics-group" key={`group-${group.key}`}>
          {group.title && (
            <span className="responses-statistics-group-title">
              {group.title}
            </span>
          )}
          {renderCharts(group.items, surveyId, valueType, filters)}
        </div>
      ))}
    </div>
  )
}
