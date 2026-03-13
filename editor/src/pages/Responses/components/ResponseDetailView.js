import React from 'react'

import { Button } from 'components'
import { QuestionPreview } from 'components/Survey'
import { getCellInfo } from '../utils'
import { getSiteUrl } from 'helpers'

export const ResponseDetailView = ({
  language,
  survey,
  onSave,
  showResponseDetails,
  canGoNextResponse,
  canGoPreviousResponse,
  rowInfo = {},
  disableUpdatingResponses = false,
}) => {
  const rowCells = rowInfo.getVisibleCells()
  const questionsInfo = {}
  const rowData = rowInfo?.original || {}

  rowCells.map((cell) => {
    const question = cell.column.columnDef?.meta?.question

    if (!question || questionsInfo[question.qid]) {
      return
    }

    questionsInfo[question.qid] = getCellInfo(cell, rowInfo)
  })

  return (
    <div key={rowData?.id} className="response-detail-view">
      <div className="response-questions">
        {Object.entries(questionsInfo).map(([, questionInfo], index) => {
          return (
            <React.Fragment key={'response-detail-view-' + index}>
              <QuestionPreview
                language={language}
                valueInfo={questionInfo}
                surveySettings={{
                  showNoAnswer: survey.showNoAnswer,
                  languages: survey.languages,
                }}
                questionNumber={questionInfo?.meta?.questionNumber}
                onSave={onSave}
                onCancel={() => {}}
                disableUpdatingResponses={disableUpdatingResponses}
              />
            </React.Fragment>
          )
        })}
      </div>
      <div className="user-response-details">
        <div>
          <p className="user-response-paragraph">
            {t('Completed')}: <i className={rowData.completed} />
          </p>
          <p className="user-response-paragraph">
            {t('Response ID')}: {rowData.id}
          </p>
          <p className="user-response-paragraph">
            {t('Start language')}: {rowData.language}
          </p>
          <p className="user-response-paragraph">
            {t('Seed')}: {rowData.seed}
          </p>
          <p className="user-response-paragraph">
            {t('Date started')}: {rowData.startDate}
          </p>
          <p className="user-response-paragraph">
            {t('Last action')}: {rowData.dateLastAction}
          </p>
          <p className="user-response-paragraph">
            {t('IP address')}:{' '}
            {rowData.ipAddr ? rowData.ipAddr : t('Not enabled')}
          </p>
          <p className="user-response-paragraph">
            {t('Referrer URL')}:{' '}
            {rowData.refUrl ? rowData.refUrl : t('Not enabled')}
          </p>
        </div>
        <div className="w-100 p-3 response-detail-view-action-buttons">
          <div className="d-flex justify-content-between gap-2 mb-3 pb-0 pt-0">
            <Button
              disabled={!canGoPreviousResponse}
              onClick={() => showResponseDetails(+rowInfo?.index - 1)}
              variant="outline-secondary"
              className="w-100 label-s d-flex justify-content-center align-content-center"
            >
              <i className="ri-arrow-left-long-line me-2"></i>
              {t('Previous')}
            </Button>
            <Button
              disabled={!canGoNextResponse}
              onClick={() => showResponseDetails(+rowInfo?.index + 1)}
              variant="outline-secondary"
              className="w-100 label-s d-flex justify-content-center align-content-center"
            >
              {t('Next')}
              <i className="ms-2 ri-arrow-right-long-fill"></i>
            </Button>
          </div>
          <Button
            href={getSiteUrl(
              `/admin/export/sa/exportresults/surveyid/${survey.sid}/id/${rowData.id}`
            )}
            className="export-response-button w-100 text-white"
          >
            {t('Export response')}
          </Button>
        </div>
      </div>
    </div>
  )
}
