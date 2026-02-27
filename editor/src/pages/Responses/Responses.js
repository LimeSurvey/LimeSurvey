import React, { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'

import { Container } from 'react-bootstrap'
import { useAppState, useResponses, useSurvey } from 'hooks'
import { createBufferOperation, htmlPopup, PAGES, STATES } from 'helpers'
import { TopBar } from 'components'

import { LeftSideBar } from './Sidebars/LeftSideBar'
import {
  ResponsesTable,
  ExportPopupHTML,
  ResponsesStatistics,
} from './components'
import { ResponsesHeader } from './ResponsesHeader'
import { TAB_KEYS } from './utils'
import { ResponsesOverview } from './components/Overview/ResponsesOverview'
import { getResponsesPanels, panelItemsKeys } from './Sidebars'
import { RightSideBar } from './Sidebars/RightSideBar'

export const Responses = () => {
  const { surveyId, menu, panel } = useParams()
  const navigate = useNavigate()
  const [filters, setFilters] = useState({})
  const [pagination, setPagination] = useState({ pageIndex: 0, pageSize: 10 })
  const [globalFilter, setGlobalFilter] = useState('')
  const [sorting, setSorting] = useState([])
  const [showTableFilters, setShowTableFilters] = useState(false)
  const [showStatisticsFilters, setShowStatisticsFilters] = useState(false)
  const [rowSelection, setRowSelection] = useState({})
  const [columnsFilters, setColumnsFilters] = useState([])
  const [tabKey, setTabKey] = useState(TAB_KEYS.RESPONSES)
  const [statisticsFilters, setStatisticsFilters] = useState({})
  const [hasResponsesUpdatePermission] = useAppState(
    STATES.HAS_RESPONSES_UPDATE_PERMISSION
  )
  const { survey = {}, fetchSurvey } = useSurvey(surveyId)
  const { responses, isFetching, mutateOperations } = useResponses(
    surveyId,
    pagination,
    filters,
    sorting
  )

  useEffect(() => {
    fetchSurvey(surveyId)
  }, [])

  useEffect(() => {
    if (menu === panelItemsKeys.statistics) {
      setTabKey(TAB_KEYS.STATISTICS)
      return
    }

    if (menu === panelItemsKeys.list) {
      setTabKey(TAB_KEYS.RESPONSES)
      return
    }

    if (menu === panelItemsKeys.overview) {
      setTabKey(TAB_KEYS.OVERVIEW)
    }
  }, [menu])

  const sortedColumnId = sorting[0]?.id ?? null

  const navigateToMenu = (menuKey) => {
    const currentPanel = panel || getResponsesPanels().results.panel

    if (menuKey === menu) {
      return
    }

    navigate(`/responses/${surveyId}/${currentPanel}/${menuKey}`)
  }

  const handleExport = () => {}

  const onExportResponsesClick = () => {
    htmlPopup({
      html: <ExportPopupHTML exportOptions={{}} />,
      showCloseButton: true,
      showCancelButton: true,
      showConfirmButton: true,
      confirmButtonText: t('Export'),
      cancelButtonText: t('Cancel'),
      closeButtonClass: 'feedback-close-button',
      popupClass: 'export-popup-container',
      confirmButtonClass: 'export-button',
      preConfirm: handleExport,
    })
  }

  const onSortChange = (sorting) => {
    setSorting(sorting)
  }

  const handleTabChange = (value) => {
    setTabKey(value)

    if (value === TAB_KEYS.STATISTICS) {
      navigateToMenu(panelItemsKeys.statistics)
      return
    }

    if (value === TAB_KEYS.RESPONSES) {
      navigateToMenu(panelItemsKeys.list)
    }
  }

  const handleResponsesUpdate = async (updateInfo) => {
    const operations = []

    for (const responseId in updateInfo) {
      const operationProps = {}
      const changes = updateInfo[responseId]
      for (const key in changes) {
        const change = changes[key]
        operationProps[key] = change === true ? 'Y' : change
      }

      const operation = createBufferOperation(responseId)
        .response()
        .update({
          ...operationProps,
        })

      operations.push(operation)
    }

    mutateOperations(operations)
  }

  const onPaginationChange = (pagination) => {
    setPagination(pagination)
  }

  const onFiltersChange = (filters) => {
    setFilters(filters)
  }

  const handleResponsesDelete = (ids) => {
    const operations = []

    ids.forEach((id) => {
      const operation = createBufferOperation(id).response().delete()
      operations.push(operation)
    })

    mutateOperations(operations)
  }

  const handleAttachmentsDelete = (ids) => {
    const operations = []

    ids.forEach((id) => {
      const operation = createBufferOperation(id).responseFile().delete()
      operations.push(operation)
    })

    mutateOperations(operations)
  }

  const renderCurrentMenu = () => {
    switch (menu) {
      case panelItemsKeys.overview:
        return (
          <ResponsesOverview
            surveyId={surveyId}
            survey={survey}
            surveyQuestions={responses.surveyQuestions}
          />
        )
      case panelItemsKeys.statistics:
        if (tabKey === TAB_KEYS.STATISTICS) {
          return (
            <ResponsesStatistics
              filters={statisticsFilters}
              surveyId={surveyId}
              isRightBar={showStatisticsFilters}
            />
          )
        }
        if (tabKey === TAB_KEYS.RESPONSES) {
          return (
            <ResponsesTable
              responsesData={responses}
              globalFilter={globalFilter}
              rowSelection={rowSelection}
              setGlobalFilter={setGlobalFilter}
              setRowSelection={setRowSelection}
              setShowFilters={setShowTableFilters}
              showFilters={showTableFilters}
              setSorting={setSorting}
              sorting={sorting}
              sortedColumnId={sortedColumnId}
              survey={survey}
              onPaginationChange={onPaginationChange}
              onFiltersChange={onFiltersChange}
              onSortChange={onSortChange}
              handleResponsesDelete={handleResponsesDelete}
              handleAttachmentsDelete={handleAttachmentsDelete}
              handleResponsesUpdate={handleResponsesUpdate}
              pagination={pagination}
              setPagination={setPagination}
              isFetching={isFetching}
              columnsFilters={columnsFilters}
              setColumnsFilters={setColumnsFilters}
              disableUpdatingResponses={!hasResponsesUpdatePermission}
            />
          )
        }
        return null
      default:
        return (
          <ResponsesOverview
            surveyId={surveyId}
            survey={survey}
            surveyQuestions={responses.surveyQuestions}
          />
        )
    }
  }

  if (!survey?.sid || !responses) {
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
          <h1 className="">{t('Loading responses...')}</h1>
        </div>
      </>
    )
  }

  return (
    <Container className="responses" fluid>
      {isFetching && (
        <div className="responses-refreshing-loader">
          <div className="spinner-border text-primary"> </div>
        </div>
      )}
      <Toaster />
      <TopBar
        surveyId={surveyId}
        showAddQuestionButton={false}
        showPublishSettings={false}
        showShareButton={false}
        showPreviewButton={false}
        showExportResponsesButton={tabKey !== TAB_KEYS.STATISTICS}
        showExportStatisticsButton={tabKey === TAB_KEYS.STATISTICS}
        onExportResponsesClick={onExportResponsesClick}
      />
      <div className="responses-body">
        <LeftSideBar
          showSidebarCloseButton={false}
          page={PAGES.RESPONSES}
          navigatePage={PAGES.EDITOR}
          surveyId={surveyId}
        />
        <div className="body-content mt-3">
          <div className="mb-3">
            <ResponsesHeader
              setShowFilters={
                tabKey === TAB_KEYS.RESPONSES
                  ? setShowTableFilters
                  : setShowStatisticsFilters
              }
              showFilters={
                tabKey === TAB_KEYS.RESPONSES
                  ? showTableFilters
                  : showStatisticsFilters
              }
              setFilters={
                tabKey === TAB_KEYS.RESPONSES
                  ? setColumnsFilters
                  : setStatisticsFilters
              }
              setTabKey={handleTabChange}
              tabKey={tabKey}
            />
          </div>
          {renderCurrentMenu()}
        </div>
        <RightSideBar
          filters={statisticsFilters}
          setFilters={setStatisticsFilters}
          tabKey={tabKey}
          showStatisticsFilters={showStatisticsFilters}
          setShowStatisticsFilters={setShowStatisticsFilters}
        />
      </div>
    </Container>
  )
}
