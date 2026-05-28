import React, { useEffect, useMemo, useState } from 'react'

import { Collapsible } from 'components'
import { useAppState, useSurvey } from 'hooks'
import { STATES } from 'helpers'

import { getDataWithPercentages } from '../../ResponsesStatistics/index.js'
import { ChartRendererV2 } from '../../ChartRenderV2.js'

const UNGROUPED_KEY = '__ungrouped__'
const COLLAPSED_GROUPS_STORAGE_PREFIX = 'responses-statistics-collapsed-groups'

const getStorageKey = (surveyId) =>
  `${COLLAPSED_GROUPS_STORAGE_PREFIX}:${surveyId ?? 'unknown'}`

const readCollapsedGroups = (surveyId) => {
  try {
    return JSON.parse(localStorage.getItem(getStorageKey(surveyId))) || {}
  } catch {
    return {}
  }
}

const writeCollapsedGroups = (surveyId, collapsed) => {
  try {
    localStorage.setItem(getStorageKey(surveyId), JSON.stringify(collapsed))
  } catch {
    // ignore
  }
}

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

const renderCharts = (items, surveyId) => (
  <div className="responses-charts row">
    {items.map(({ item, index }) => {
      const chartId =
        item?.meta?.question?.qid ?? item?.meta?.question?.id ?? item?.title
      return (
        <div className="col-12" key={`responses-charts-${index}`}>
          <ChartRendererV2
            index={index}
            surveyId={surveyId}
            chartId={chartId}
            data={getDataWithPercentages(item)}
            graphType={item}
            question={{
              type: item?.meta?.question?.type,
              code: item?.meta?.question?.code,
              title: item?.title,
              help: item?.meta?.question?.help,
              index: index,
            }}
          />
        </div>
      )
    })}
  </div>
)

export const StatisticsContainer = ({ statistics, surveyId }) => {
  const { survey } = useSurvey(surveyId)
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
  const [collapsedGroups, setCollapsedGroups] = useState(() =>
    readCollapsedGroups(surveyId)
  )

  useEffect(() => {
    setCollapsedGroups(readCollapsedGroups(surveyId))
  }, [surveyId])

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

  const toggleGroup = (groupKey, open) => {
    const next = { ...collapsedGroups }
    if (open) {
      delete next[groupKey]
    } else {
      next[groupKey] = true
    }
    writeCollapsedGroups(surveyId, next)
    setCollapsedGroups(next)
  }

  return (
    <div className="responses-statistics-body">
      {grouped.map((group) => {
        const open = !collapsedGroups[group.key]
        const hasTitle = !!group.title
        return (
          <div
            className="responses-statistics-group"
            key={`group-${group.key}`}
          >
            {hasTitle ? (
              <Collapsible
                text={group.title}
                open={open}
                onToggle={(next) => toggleGroup(group.key, next)}
              >
                {renderCharts(group.items, surveyId)}
              </Collapsible>
            ) : (
              renderCharts(group.items, surveyId)
            )}
          </div>
        )
      })}
    </div>
  )
}
