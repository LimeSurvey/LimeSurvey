import React, { useCallback, useEffect, useRef, useState } from 'react'
import {
  useReactTable,
  getCoreRowModel,
  getSortedRowModel,
  getPaginationRowModel,
  getFacetedRowModel,
  getFacetedUniqueValues,
} from '@tanstack/react-table'
import { debounce } from 'lodash'
import classNames from 'classnames'

import { PaginationButtons } from 'components'
import { QuestionPreview } from 'components/Survey/Questions/QuestionPreview'
import {
  getDefaultColumns,
  generateColumns,
  generateData,
  getSelectedRowIdsFromTable,
  SelectColumnId,
  ActionsColumnId,
} from '../../utils'
import { Toast } from 'helpers'

import { BulkActions, ResponseDetailView } from '../'
import { ResponseModals } from '../'
import { ResponsesTableHeader } from './ResponsesTableHeader'
import { ResponsesTableBody } from './ResponsesTableBody'

export const ResponsesTable = ({
  survey,
  showFilters,
  sortedColumnId,
  sorting,
  setSorting,
  responsesData,
  onFiltersChange = () => {},
  handleResponsesDelete = () => {},
  handleAttachmentsDelete = () => {},
  handleResponsesUpdate = () => {},
  pagination,
  setPagination,
  isFetching = false,
  columnsFilters,
  setColumnsFilters,
  surveyQuestions = [],
  hidePaginationButtons = false,
  hideActions = false,
  hideSelect = false,
  disableUpdatingResponses = false,
}) => {
  const [firstLoad, setFirstLoad] = useState(true)
  const [data, setData] = useState([])
  const [columns, setColumns] = useState([])
  const clickedRowRef = useRef({})
  const isBulkActionRef = useRef(false)
  const [responseViewRowInfo, setResponseViewRowInfo] = useState(null)
  const [showColumnManagementModal, setShowColumnManagementModal] =
    useState(false)
  const [showResponsesDeleteModal, setShowResponsesDeleteModal] =
    useState(false)
  const [showAttachmentsDeleteModal, setShowAttachmentsDeleteModal] =
    useState(false)
  const [showFiltersColumn, setShowFiltersColumn] = useState(false)
  const [showQuestionComponent, setShowQuestionComponent] = useState(false)
  const [showSurveyDetails, setShowSurveyDetails] = useState(false)
  const [columnVisibility, setColumnVisibility] = useState({})
  const cellQuestionInfoRef = useRef({})
  const [showResponseInfoOnNextPage, setShowResponseInfoOnNextPage] =
    useState(false)

  const [columnsOrder, setColumnsOrder] = useState([])
  const [pinnedColumns, setPinnedColumns] = useState([SelectColumnId])

  const table = useReactTable({
    data,
    columns,
    state: {
      sorting,
      pagination,
      columnVisibility,
      columnOrder: columnsOrder,
      columnPinning: {
        left: pinnedColumns,
        right: [ActionsColumnId],
      },
    },
    defaultColumn: {
      size: 80,
      maxSize: 1000,
      minSize: 200,
    },
    onColumnOrderChange: setColumnsOrder,
    onSortingChange: setSorting,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getFacetedRowModel: getFacetedRowModel(),
    rowCount: responsesData._meta?.pagination?.totalItems,
    getFacetedUniqueValues: getFacetedUniqueValues(),
    enableRowSelection: true,
    onPaginationChange: setPagination,
    manualPagination: true,
    manualFiltering: true,
    manualSorting: true,
    columnResizeMode: 'onChange',
    enableColumnResizing: true,
    getRowId: (row, index) => {
      return row?.id === undefined ? index : row?.id
    },
  })

  useEffect(() => {
    if (!columnsFilters || firstLoad) {
      return
    }

    handleOnFilterChange(columnsFilters)
  }, [columnsFilters])

  const handleOnFilterChange = useCallback(
    debounce((columnsFilters) => {
      onFiltersChange(columnsFilters)
      // redirect to the first page when filters change
      setPagination({ ...pagination, pageIndex: 0 })
    }, 1000),
    [survey.sid]
  )

  useEffect(() => {
    if (!responsesData || !survey.sid) {
      return
    }

    const defaultColumns = getDefaultColumns({
      onResponseDetailViewClick: showSurveyPreview,
      deleteCallback: handleResponseDelete,
      showColumnsManagment: () => setShowColumnManagementModal(true),
      onDownloadAllFilesClick: () => handleDownloadAllFiles(false),
      onDeleteResponseFilesClick: () => handleOnActionFilesDeleteClick(),
    })

    let generatedColumns = columns

    // if the columns are not generated yet, generate them
    if (!columns.length) {
      generatedColumns = generateColumns(
        responsesData.surveyQuestions || surveyQuestions,
        survey
      )

      if (!hideSelect) {
        generatedColumns = [defaultColumns.SELECT, ...generatedColumns]
      }

      if (!hideActions) {
        generatedColumns = [...generatedColumns, defaultColumns.ACTIONS]
      }

      setColumns(generatedColumns)
      // else if we have columns, then we pop the actions column and readd it to update the columns ref
    } else if (!hideActions && columns.length) {
      columns.pop()
      setColumns([...columns, defaultColumns.ACTIONS])
    }

    setFirstLoad(false)
    setData(
      generateData(responsesData.responses, survey.language, generatedColumns)
    )
  }, [responsesData, survey.sid])

  useEffect(() => {
    // set the columns order only once after the columns are generated and then control it with the columns manager.
    if (!responsesData || !columns.length || firstLoad || columnsOrder.length) {
      return
    }

    setColumnsOrder(columns.map((c) => c?.id))
  }, [columns])

  useEffect(() => {
    if (table.getSelectedRowModel().rows.length > 0) {
      table.resetRowSelection()
    }
  }, [sorting, columnsFilters, pagination])

  const handleResponseDelete = () => {
    isBulkActionRef.current = false
    setShowResponsesDeleteModal(true)
  }

  const handleDownloadAllFiles = (isBulkActions = true) => {
    const row = clickedRowRef.current
    const currentSelectedRowId = row.original?.id
    const hasFiles = row.original?.hasFiles
    const selectedRowsIds = getSelectedRowIdsFromTable(table)
    const responseIdsToDownload = isBulkActions
      ? selectedRowsIds
      : [currentSelectedRowId]

    if (hasFiles) {
      window.open(
        `${location.protocol}//${location.hostname}/responses/downloadfiles?surveyId=${survey.sid}&responseIds=${responseIdsToDownload.join(',')}`
      )
    } else {
      Toast({
        message: t('No files to download!'),
        position: 'bottom-center',
        duration: 2000,
      })
    }
  }

  const showResponseDetails = (responseTableIndex) => {
    if (responseTableIndex === pagination.pageSize && table.getCanNextPage()) {
      table.nextPage()
      setShowResponseInfoOnNextPage('first')
      return
    } else if (responseTableIndex === -1 && table.getCanPreviousPage()) {
      table.previousPage()
      setShowResponseInfoOnNextPage('last')
      return
    }

    if (responseTableIndex !== -1 && responseTableIndex < pagination.pageSize) {
      const rowInfo = table.getRowModel().rows[responseTableIndex]
      showSurveyPreview(rowInfo)
    }
  }

  const showSurveyPreview = (_row = null) => {
    const row = _row ? _row : clickedRowRef.current

    setResponseViewRowInfo(row)
    setShowSurveyDetails(true)
  }

  const handleOnSave = (valuesInfo, row) => {
    const updateValue = {}
    const responseId = row.original.id

    for (const key in valuesInfo) {
      updateValue[responseId] = {
        ...updateValue[[responseId]],
        [key]: valuesInfo[key],
      }
    }

    handleResponsesUpdate(updateValue)
    setShowQuestionComponent(false)
  }

  const onResponsesDeleteConfirm = () => {
    const selectedRowsIds = isBulkActionRef.current
      ? getSelectedRowIdsFromTable(table)
      : [clickedRowRef.current.original.id]

    handleResponsesDelete(selectedRowsIds, false)
  }

  const handleOnActionFilesDeleteClick = () => {
    setShowAttachmentsDeleteModal(true)
  }

  const handleDeletingFiles = () => {
    const row = clickedRowRef.current
    const currentSelectedRowId = row.original?.id
    const selectedRowsIds = getSelectedRowIdsFromTable(table)

    const responseIdsToDeleteFiles = isBulkActionRef.current
      ? selectedRowsIds
      : [currentSelectedRowId]

    handleAttachmentsDelete(responseIdsToDeleteFiles)
    isBulkActionRef.current = false
  }

  const handleOnColumnsManagementConfirm = (columnsInfo) => {
    const columnOrder = columnsInfo.map((column) => column.id)
    const columnVisibility = Object.assign(
      {},
      ...columnsInfo.map((column) => {
        return { [column.id]: column.checked }
      })
    )

    setColumnVisibility(columnVisibility)
    setColumnsOrder(columnOrder)
  }

  useEffect(() => {
    if (isFetching) {
      return
    }

    try {
      const rows = table.getRowModel().rows
      if (showResponseInfoOnNextPage === 'first') {
        clickedRowRef.current = rows[0]
        showSurveyPreview(clickedRowRef.current)
      } else if (showResponseInfoOnNextPage === 'last') {
        clickedRowRef.current = rows[rows.length - 1]
        showSurveyPreview(clickedRowRef.current)
      }
    } catch (e) {
      // eslint-disable-next-line no-console
      console.error(e)
    } finally {
      setShowResponseInfoOnNextPage(false)
    }
  }, [data])

  return (
    <>
      <div className="responses-table">
        <div className="table-container">
          <table className="table table-hover align-middle">
            <ResponsesTableHeader
              pinnedColumns={pinnedColumns}
              setPinnedColumns={setPinnedColumns}
              table={table}
            />
            <ResponsesTableBody
              cellQuestionInfoRef={cellQuestionInfoRef}
              clickedRowRef={clickedRowRef}
              columnsFilters={columnsFilters}
              setColumnsFilters={setColumnsFilters}
              setShowQuestionComponent={setShowQuestionComponent}
              showFilters={showFilters}
              sid={survey.sid}
              sortedColumnId={sortedColumnId}
              table={table}
            />
          </table>
        </div>

        <div
          className={classNames(
            'd-flex justify-content-center align-content-center py-1 px-4',
            { 'd-none': hidePaginationButtons }
          )}
        >
          <PaginationButtons
            OnPageNumberClick={(number) => table.setPageIndex(number)}
            canGoNextPage={table.getCanNextPage()}
            canGoPrevPage={table.getCanPreviousPage()}
            currentPageIndex={pagination.pageIndex}
            maxNumberOfButtons={5}
            onFirstPageClick={table.firstPage}
            onLastPageClick={table.lastPage}
            onNextPageClick={table.nextPage}
            onPrevPageClick={table.previousPage}
            totalPages={table.getPageCount()}
            onPageSizeChange={(value) => {
              table.setPageSize(value)
              table.firstPage()
            }}
            pageSize={pagination.pageSize}
          />
        </div>
      </div>
      <BulkActions
        table={table}
        onDeleteClick={() => {
          setShowResponsesDeleteModal(true)
          isBulkActionRef.current = true
        }}
        onAttachmentsDeleteClick={() => {
          setShowAttachmentsDeleteModal(true)
          isBulkActionRef.current = true
        }}
        onDownloadFilesClick={handleDownloadAllFiles}
      />
      <ResponseModals
        showResponsesDeleteModal={showResponsesDeleteModal}
        setShowResponsesDeleteModal={setShowResponsesDeleteModal}
        setShowAttachmentsDeleteModal={setShowAttachmentsDeleteModal}
        onAttachmentsDeleteConfirm={handleDeletingFiles}
        showAttachmentsDeleteModal={showAttachmentsDeleteModal}
        showFiltersColumn={showFiltersColumn}
        setShowFiltersColumn={setShowFiltersColumn}
        showSurveyDetails={showSurveyDetails}
        setShowSurveyDetails={setShowSurveyDetails}
        showQuestionComponent={showQuestionComponent}
        setShowQuestionComponent={setShowQuestionComponent}
        setShowColumnManagementModal={setShowColumnManagementModal}
        showColumnManagementModal={showColumnManagementModal}
        onResponsesDeleteConfirm={() => onResponsesDeleteConfirm()}
        handleOnColumnsManagementConfirm={handleOnColumnsManagementConfirm}
        handleOnHide={() => {
          isBulkActionRef.current = false
        }}
        table={table}
        QuestionComponent={
          <QuestionPreview
            surveySettings={{
              showNoAnswer: survey.showNoAnswer,
              languages: survey.languages,
            }}
            language={survey.language}
            valueInfo={cellQuestionInfoRef.current}
            onSave={(valueInfo) =>
              handleOnSave(valueInfo, clickedRowRef.current)
            }
            onCancel={() => setShowQuestionComponent(false)}
            clickedRowRef={clickedRowRef}
            disableUpdatingResponses={disableUpdatingResponses}
          />
        }
        SurveyDetailsComponent={
          <ResponseDetailView
            language={survey.language}
            survey={survey}
            meta={{ ...clickedRowRef.current?.original?.meta }}
            onSave={(valueInfo) =>
              handleOnSave(valueInfo, clickedRowRef.current)
            }
            OnCancel={() => setShowQuestionComponent(false)}
            canGoNextResponse={
              table.getCanNextPage() ||
              responseViewRowInfo?.index < table.getRowModel().rows.length - 1
            }
            canGoPreviousResponse={
              table.getCanPreviousPage() || responseViewRowInfo?.index > 0
            }
            rowInfo={{ ...responseViewRowInfo }}
            showResponseDetails={showResponseDetails} // to navigate between responses.
            disableUpdatingResponses={disableUpdatingResponses}
          />
        }
      />
    </>
  )
}
