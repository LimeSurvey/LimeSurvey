import { useEffect, useState } from 'react'
import { Card } from 'react-bootstrap'
import { useNavigate } from 'react-router-dom'
import { cloneDeep } from 'lodash'
import { format } from 'date-fns'

import { ResponseService } from 'services'
import { useAuth } from 'hooks'
import { getApiUrl } from 'helpers'

import { ChartRenderer } from '../ChartRenderer'
import {
  getDataWithPercentages,
  statisticsGraphs,
} from '../ResponsesStatistics'
import { ResponsesTable } from '../ResponsesTable'
import { getResponsesPanels, panelItemsKeys } from '../../Sidebars'

export const ResponsesOverview = ({ surveyId, survey, surveyQuestions }) => {
  const navigate = useNavigate()
  const [data, setData] = useState(null)
  const [dailyActivityData, setDailyActivityData] = useState(null)
  const [lurkerData, setLurkerData] = useState(null)
  const [overview = {}, setOverview] = useState(null)
  const auth = useAuth()

  useEffect(() => {
    new ResponseService(auth, surveyId, getApiUrl())
      .getResponsesOverview(surveyId)
      .then((result) => {
        setData(result?.overview)
        const dailyActivity = cloneDeep(result.overview.dailyActivity)
        dailyActivity.data = dailyActivity.data.map((item) => ({
          ...item,
          key: format(item.key, 'MM-dd'),
          title: format(item.title, 'MM-dd'),
        }))
        dailyActivity.legend = dailyActivity.legend.map((item) =>
          format(item, 'MM-dd')
        )

        const statistics = result.overview.statistics
        const lurkerData = {
          title: t('Responses rate'),
          total: statistics.totalResponses,
          legend: [
            'totalResponses',
            'incompleteResponses',
            'completedWithoutAnswers',
            'incompletedWithoutAnswers',
          ],
          data: [
            {
              title: t('Complete responses'),
              key: 'incompleteResponses',
              value: statistics.totalResponses - statistics.incompleteResponses,
            },
            {
              title: t('Completed without answers'),
              key: 'completedWithoutAnswers',
              value: statistics.completedWithoutAnswers,
            },
            {
              title: t('Incompleted without answers'),
              key: 'incompletedWithoutAnswers',
              value: statistics.incompletedWithoutAnswers,
            },
          ],
        }

        setOverview(result.overview.statistics)
        setLurkerData(getDataWithPercentages(lurkerData))
        setDailyActivityData(getDataWithPercentages(dailyActivity))
      })
  }, [surveyId])

  if (!data || !dailyActivityData || !lurkerData || !survey.sid) {
    return (
      <>
        <div
          style={{ height: '100vh' }}
          className="d-flex flex-column justify-content-center align-items-center"
        >
          <span
            style={{ width: 48, height: 48 }}
            className="loader mb-4"
          ></span>
          <h1>{t('Loading statistics...')}</h1>
        </div>
      </>
    )
  }

  return (
    <div className="mb-3">
      <div className="row g-4 mb-3 pb-1">
        <div className="col-md-3">
          <Card className="h-100 w-100">
            <Card.Body className="d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
              <span className="reg28 text-nowrap">
                {overview.totalResponses}
              </span>
              <span className="reg14">{t('Total responses')}</span>
            </Card.Body>
          </Card>
        </div>
        <div className="col-md-3">
          <Card className="h-100 w-100">
            <Card.Body className="d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
              <span className="reg28 text-nowrap">
                {overview.totalResponses - overview.incompleteResponses}
              </span>
              <span className="reg14">{t('Full responses')}</span>
            </Card.Body>
          </Card>
        </div>
        <div className="col-md-3">
          <Card className="h-100 w-100">
            <Card.Body className="d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
              {overview?.completionRate ? (
                <>
                  <span className="reg28 text-nowrap">
                    {`${overview.completionRate}%`}
                  </span>
                  <span className="reg14">{t('Response rate')}</span>
                </>
              ) : (
                <span className="reg14">{t('No responses yet.')}</span>
              )}
            </Card.Body>
          </Card>
        </div>
        <div className="col-md-3">
          <Card className="h-100 w-100">
            <Card.Body className="d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
              <span className="reg28 text-nowrap">
                {overview.incompleteResponses}
              </span>
              <span className="reg14">{t('Incomplete responses')}</span>
            </Card.Body>
          </Card>
        </div>
      </div>
      <div className="row">
        <div className="col-xxl-6 col-12 mb-2">
          <ChartRenderer
            graphType={statisticsGraphs.BAR_CHART}
            title={t('Responses last 30 day')}
            statisticsData={survey.datestamp ? dailyActivityData : []}
            disableDoughnutChart={true}
            disableBarChart={true}
            disableLineChart={true}
            disableRadarChart={true}
            disablePieChart={true}
            emptyMessage={
              !survey.datestamp
                ? t(
                    'Daily activity isn’t available when datestamps are turned off.'
                  )
                : null
            }
          />
        </div>
        <div className="col-xxl-6 col-12 mb-2">
          <ChartRenderer
            graphType={statisticsGraphs.PIE_CHART}
            statisticsData={lurkerData}
            title={t('Completed / Incomplete / Lurkers')}
            filterZeroValues={true}
            disableDoughnutChart={true}
            disableBarChart={true}
            disableLineChart={true}
            disableRadarChart={true}
            disablePieChart={true}
          />
        </div>
      </div>
      <div className="position-relative mt-4">
        <ResponsesTable
          responsesData={{ responses: data.responses.slice(0, 3) }}
          pagination={{}}
          survey={survey}
          surveyQuestions={surveyQuestions}
          hidePaginationButtons={true}
          hideActions={true}
          hideSelect={true}
          disableUpdatingResponses={true}
        />
        <div
          onClick={() =>
            navigate(
              `/responses/${surveyId}/${getResponsesPanels().results.panel}/${panelItemsKeys.list}`
            )
          }
          className="text-end mt-2 med14-c cursor-pointer text-primary"
        >
          {t('View all responses')} <i className="ri-arrow-right-line"></i>
        </div>
      </div>
    </div>
  )
}
