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
  const [rowSelection, setRowSelection] = useState({})
  const [persistentSelection, setPersistentSelection] = useState({})
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

  const handleRowSelectionChange = (updaterOrValue) => {
    const newSelection =
      typeof updaterOrValue === 'function'
        ? updaterOrValue(rowSelection)
        : updaterOrValue

    setRowSelection(newSelection)

    // Sync with persistent selection
    setPersistentSelection((prev) => {
      const updated = { ...prev }

      // Remove deselected rows (rows on current page that are no longer selected)
      const currentPageRowIds = data.map((row) =>
        row?.id === undefined ? '' : String(row.id)
      )
      currentPageRowIds.forEach((id) => {
        if (!newSelection[id]) {
          delete updated[id]
        }
      })

      // Add newly selected rows
      Object.keys(newSelection).forEach((id) => {
        if (newSelection[id]) {
          updated[id] = true
        }
      })

      return updated
    })
  }

  const table = useReactTable({
    data,
    columns,
    state: {
      sorting,
      pagination,
      rowSelection,
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
    onRowSelectionChange: handleRowSelectionChange,
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

  // Reset selection when sorting or filters change (dataset identity changes)
  useEffect(() => {
    setRowSelection({})
    setPersistentSelection({})
  }, [sorting, columnsFilters])

  // Restore checkboxes from persistentSelection when page data changes
  useEffect(() => {
    if (!data.length) return
    const restoredSelection = {}
    data.forEach((row) => {
      const id = row?.id === undefined ? '' : String(row.id)
      if (persistentSelection[id]) {
        restoredSelection[id] = true
      }
    })
    setRowSelection(restoredSelection)
  }, [data])

  const handleResponseDelete = () => {
    isBulkActionRef.current = false
    setShowResponsesDeleteModal(true)
  }

  const handleDownloadAllFiles = (isBulkActions = true) => {
    const row = clickedRowRef.current
    const currentSelectedRowId = row.original?.id
    const hasFiles = row.original?.hasFiles
    const selectedRowsIds = Object.keys(persistentSelection)
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
      ? Object.keys(persistentSelection)
      : [clickedRowRef.current.original.id]

    handleResponsesDelete(selectedRowsIds, false)
    if (isBulkActionRef.current) {
      setPersistentSelection({})
      setRowSelection({})
    }
  }

  const handleOnActionFilesDeleteClick = () => {
    setShowAttachmentsDeleteModal(true)
  }

  const handleDeletingFiles = () => {
    const row = clickedRowRef.current
    const currentSelectedRowId = row.original?.id
    const selectedRowsIds = Object.keys(persistentSelection)

    const responseIdsToDeleteFiles = isBulkActionRef.current
      ? selectedRowsIds
      : [currentSelectedRowId]

    handleAttachmentsDelete(responseIdsToDeleteFiles)
    if (isBulkActionRef.current) {
      setPersistentSelection({})
      setRowSelection({})
    }
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
        selectedCount={Object.keys(persistentSelection).length}
        onDeleteClick={() => {
          setShowResponsesDeleteModal(true)
          isBulkActionRef.current = true
        }}
        onAttachmentsDeleteClick={() => {
          setShowAttachmentsDeleteModal(true)
          isBulkActionRef.current = true
        }}
        onDownloadFilesClick={handleDownloadAllFiles}
        onUnselectAll={() => {
          setPersistentSelection({})
          setRowSelection({})
        }}
      />
      <ResponseModals
        showResponsesDeleteModal={showResponsesDeleteModal}
        isBulkAction={isBulkActionRef.current}
        selectedRowsIds={Object.keys(persistentSelection)}
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
