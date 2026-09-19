import React, { useEffect, useRef, useState } from 'react'
import { useParams, useSearchParams } from 'react-router-dom'
import { Toaster } from 'react-hot-toast'
import { useTranslation } from 'react-i18next'

import { Container } from 'react-bootstrap'
import { useAppState, useResponses, useSetAllLanguages, useSurvey } from 'hooks'
import {
  createBufferOperation,
  downloadBlob,
  getFilenameFromContentDisposition,
  PAGES,
  STATES,
  toastComponent,
} from 'helpers'
import { ComponentModal } from 'components'

import { LeftSideBar } from './Sidebars/LeftSideBar'
import {
  ResponsesTable,
  ExportResponsesModal,
  ResponsesStatistics,
} from './components'
import { ResponsesHeader } from './ResponsesHeader'
import { TAB_KEYS } from './utils'
import { ResponsesOverview } from './components/Overview/ResponsesOverview'
import { panelItemsKeys } from './Sidebars'
import { RightSideBar } from './Sidebars/RightSideBar'

export const Responses = () => {
  const { t } = useTranslation()
  const { surveyId, menu } = useParams()
  const [searchParams, setSearchParams] = useSearchParams()
  const deepLinkResponseId = searchParams.get('id')
  const [filters, setFilters] = useState({})
  const [pagination, setPagination] = useState(() => ({
    pageIndex: Math.max(Number(searchParams.get('page')) - 1, 0),
    pageSize: Number(searchParams.get('size')) || 10,
  }))
  const [globalFilter, setGlobalFilter] = useState('')
  const [sorting, setSorting] = useState([])
  const [showTableFilters, setShowTableFilters] = useState(false)
  const [showStatisticsFilters, setShowStatisticsFilters] = useState(false)
  const [columnsFilters, setColumnsFilters] = useState([])
  const [tabKey, setTabKey] = useState(TAB_KEYS.RESPONSES)
  const [statisticsFilters, setStatisticsFilters] = useState({})
  const [showExportModal, setShowExportModal] = useState(false)
  const exportOptionsRef = useRef(null)
  const [hasResponsesUpdatePermission] = useAppState(
    STATES.HAS_RESPONSES_UPDATE_PERMISSION
  )
  const [, setTopbarConfig] = useAppState(STATES.TOPBAR_CONFIG, {})
  const {
    survey = {},
    fetchSurvey,
    refetchQuestionsFieldNamesMap,
  } = useSurvey(surveyId)
  const { fetchAllLanguages } = useSetAllLanguages()
  const {
    responses,
    isFetching,
    mutateOperations,
    exportResponses,
    isExporting,
  } = useResponses(surveyId, pagination, filters, sorting)

  // Responses page isn't wrapped by EditorContextController, so fetch languages here
  // to show full language names (not just codes) in the export modal.
  useEffect(() => {
    if (survey.sid) {
      fetchAllLanguages(survey.languages)
    }
  }, [survey.sid])

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

  const handleExport = async () => {
    const exportData = exportOptionsRef.current
    if (!exportData || !exportData.options) {
      toastComponent({
        Component: <span>{t('Export options not initialized')}</span>,
      })
      return
    }

    const exportPayload = {
      ...exportData.options,
      filters: exportData.options.responseType === 'filtered' ? filters : {},
    }

    try {
      const response = await exportResponses(exportPayload)
      const filename = getFilenameFromContentDisposition(
        response.headers['content-disposition'],
        `responses.${exportData.options.type}`
      )
      downloadBlob(response.data, filename)
      setShowExportModal(false)
    } catch (error) {
      toastComponent({
        Component: (
          <span>
            {t('Export failed')}: {error.message}
          </span>
        ),
      })
    }
  }

  const onExportResponsesClick = () => {
    setShowExportModal(true)
  }

  const onSortChange = (sorting) => {
    setSorting(sorting)
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

  // Keep page/size in the URL at all times so links are always shareable.
  useEffect(() => {
    const next = new URLSearchParams(searchParams)
    next.set('page', String(pagination.pageIndex + 1))
    next.set('size', String(pagination.pageSize))
    setSearchParams(next, { replace: true })
  }, [pagination.pageIndex, pagination.pageSize])

  const handleResponseModalOpen = (responseId) => {
    const next = new URLSearchParams(searchParams)
    next.set('id', String(responseId))
    setSearchParams(next)
  }

  const handleResponseModalClose = () => {
    const next = new URLSearchParams(searchParams)
    next.delete('id')
    setSearchParams(next)
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

  useEffect(() => {
    fetchSurvey(surveyId)
    refetchQuestionsFieldNamesMap()
    setTopbarConfig({
      surveyId,
      showAddQuestionButton: false,
      showPublishSettings: false,
      showShareButton: false,
      showPreviewButton: false,
      showExportResponsesButton: tabKey !== TAB_KEYS.STATISTICS,
      showExportStatisticsButton: tabKey === TAB_KEYS.STATISTICS,
      onExportResponsesClick,
      pageName: PAGES.RESPONSES,
    })
  }, [tabKey, surveyId])

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
        return (
          <ResponsesStatistics
            filters={statisticsFilters}
            surveyId={surveyId}
            isRightBar={showStatisticsFilters}
            showFilters={showStatisticsFilters}
            setShowFilters={setShowStatisticsFilters}
            setFilters={setStatisticsFilters}
          />
        )
      case panelItemsKeys.list:
        if (tabKey === TAB_KEYS.RESPONSES) {
          return (
            <ResponsesTable
              responsesData={responses}
              globalFilter={globalFilter}
              setGlobalFilter={setGlobalFilter}
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
              deepLinkResponseId={deepLinkResponseId}
              onResponseModalOpen={handleResponseModalOpen}
              onResponseModalClose={handleResponseModalClose}
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
      <ComponentModal
        show={showExportModal}
        onHide={() => setShowExportModal(false)}
        title={t('Export results')}
        headerClassname="export-results-modal-header"
        Component={
          <ExportResponsesModal
            surveyLanguage={survey?.language}
            additionalLanguages={survey?.additionalLanguages}
            exportRef={exportOptionsRef}
          />
        }
        componentClassname="export-responses-modal"
        modalClassname="export-results-modal"
        useFooter
        confirmButtonText={
          isExporting ? t('Exporting...') : t('Export results')
        }
        onConfirm={handleExport}
        isLoading={isExporting}
      />
      <div className="responses-body">
        <LeftSideBar
          showSidebarCloseButton={false}
          page={PAGES.RESPONSES}
          navigatePage={PAGES.EDITOR}
          surveyId={surveyId}
        />
        <div className="body-content mt-3">
          {tabKey !== TAB_KEYS.STATISTICS && (
            <div className="mb-3">
              <ResponsesHeader
                setShowFilters={setShowTableFilters}
                showFilters={showTableFilters}
                setFilters={setColumnsFilters}
                tabKey={tabKey}
              />
            </div>
          )}
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
