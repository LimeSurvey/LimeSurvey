import { useEffect, useMemo, useState } from 'react'
import { format } from 'util'

import {
  Button,
  HighlightedText,
  LSTable,
  SearchInput,
  useSearchTerms,
} from 'components'
import { htmlToPlainText } from 'helpers'
import { useQuestionResponses } from 'hooks'
import { useIsInViewport } from 'hooks/useInViewport'

import { StatisticsDetailModal } from './StatisticsDetailModal.js'

// The card shows a short preview; the full list lives in the modal.
const INLINE_LIMIT = 5

const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']

const safeDecode = (value) => {
  try {
    return decodeURIComponent(value)
  } catch {
    return value
  }
}

const extensionOf = (name) => name.split('.').pop()?.toLowerCase() ?? ''

// Sizes are stored in kilobytes by the uploader.
const formatFileSize = (sizeKb) => {
  const kb = Number(sizeKb) || 0
  if (kb < 1024) {
    return `${Math.max(Math.round(kb), 1)} ${t('KB')}`
  }
  return `${(kb / 1024).toLocaleString(undefined, {
    maximumFractionDigits: 1,
  })} ${t('MB')}`
}

const fileUrl = (surveyId, responseId, questionId, index, inline = false) =>
  `${location.origin}/responses/downloadfile?surveyId=${surveyId}&responseId=${responseId}&qid=${questionId}&index=${index}${inline ? '&inline=1' : ''}`

const parseFiles = (value) => {
  if (!value || typeof value !== 'string') {
    return []
  }
  try {
    const files = JSON.parse(value)
    return Array.isArray(files) ? files : []
  } catch {
    return []
  }
}

const FileThumbnail = ({ file, previewUrl }) => {
  if (file.isImage) {
    return (
      <img
        className="responses-statistics-files-thumb"
        src={previewUrl}
        alt={file.name}
        loading="lazy"
      />
    )
  }
  return (
    <span className="responses-statistics-files-thumb responses-statistics-files-thumb--icon">
      <i className="ri-file-text-line"></i>
    </span>
  )
}

/**
 * Uploaded files of a file upload question (|): one table row per file across
 * the loaded responses, with preview and download actions.
 */
export const FileUploadTable = ({
  surveyId,
  questionId,
  questionCode,
  title = '',
  fields,
  filters,
}) => {
  const [containerRef, isInView] = useIsInViewport(null, {
    initialInView: false,
  })
  const [shouldLoad, setShouldLoad] = useState(false)
  const [showModal, setShowModal] = useState(false)
  const [previewFile, setPreviewFile] = useState(null)
  useEffect(() => {
    if (isInView) {
      setShouldLoad(true)
    }
  }, [isInView])

  const { terms, setTerms, setTyped, search } = useSearchTerms()

  const highlightTerms = useMemo(
    () => [...new Set([...(filters?.search ?? []), ...search])],
    [filters, search]
  )

  // Only the JSON column holds the files; the numeric Cfilecount column must
  // stay out of the text search.
  const fileFields = useMemo(
    () => (fields ?? []).filter((field) => !field.endsWith('filecount')),
    [fields]
  )

  const {
    columns,
    rows,
    totalResults,
    isLoading,
    hasNextPage,
    fetchNextPage,
    isFetchingNextPage,
  } = useQuestionResponses(surveyId, questionCode, {
    enabled: shouldLoad,
    fields: fileFields,
    filters,
    search,
  })

  const answerKey = columns[0]?.key
  const files = useMemo(
    () =>
      rows.flatMap((row) =>
        parseFiles(row.cells?.[answerKey])
          .map((file, index) => ({ ...file, index }))
          .filter((file) => !file.isDeleted)
          .map((file) => {
            const name = safeDecode(file.name ?? '')
            const extension = extensionOf(name)
            return {
              id: `${row.responseId}-${file.index}`,
              responseId: row.responseId,
              index: file.index,
              name,
              extension,
              isImage: IMAGE_EXTENSIONS.includes(extension),
              title: file.title ?? '',
              comment: file.comment ?? '',
              size: file.size,
            }
          })
      ),
    [rows, answerKey]
  )

  const tableColumns = useMemo(
    () => [
      {
        id: 'file',
        header: t('File'),
        cell: ({ row }) => {
          const file = row.original
          return (
            <div className="responses-statistics-files-file">
              <FileThumbnail
                file={file}
                previewUrl={fileUrl(
                  surveyId,
                  file.responseId,
                  questionId,
                  file.index,
                  true
                )}
              />
              <span className="responses-statistics-files-name">
                <HighlightedText text={file.name} terms={highlightTerms} />
              </span>
            </div>
          )
        },
      },
      {
        id: 'title',
        header: t('Title'),
        cell: ({ row }) => (
          <div className="responses-statistics-array-text-cell">
            <HighlightedText
              text={htmlToPlainText(row.original.title) || '-'}
              terms={highlightTerms}
            />
          </div>
        ),
      },
      {
        id: 'comment',
        header: t('Comment'),
        cell: ({ row }) => (
          <div className="responses-statistics-array-text-cell">
            <HighlightedText
              text={htmlToPlainText(row.original.comment) || '-'}
              terms={highlightTerms}
            />
          </div>
        ),
      },
      {
        id: 'size',
        header: t('Size'),
        cell: ({ row }) => formatFileSize(row.original.size),
      },
      {
        id: 'actions',
        header: t('Actions'),
        cell: ({ row }) => {
          const file = row.original
          return (
            <div className="responses-statistics-files-actions">
              {file.isImage && (
                <button
                  type="button"
                  onClick={() => setPreviewFile(file)}
                  title={t('Preview')}
                >
                  <i className="ri-eye-line"></i>
                </button>
              )}
              <a
                href={fileUrl(
                  surveyId,
                  file.responseId,
                  questionId,
                  file.index
                )}
                title={t('Download')}
              >
                <i className="ri-download-line"></i>
              </a>
            </div>
          )
        },
      },
    ],
    [surveyId, questionId, highlightTerms]
  )

  const previewModal = (
    <StatisticsDetailModal
      show={previewFile !== null}
      onHide={() => setPreviewFile(null)}
      modalClassname="responses-statistics-files-preview"
    >
      {previewFile && (
        <div className="responses-statistics-files-preview-body">
          <h2 className="responses-statistics-modal-title">
            {htmlToPlainText(previewFile.title) || previewFile.name}
          </h2>
          <span className="responses-statistics-files-preview-name">
            {previewFile.name}
          </span>
          {previewFile.comment && (
            <p className="responses-statistics-files-preview-comment">
              {htmlToPlainText(previewFile.comment)}
            </p>
          )}
          <img
            className="responses-statistics-files-preview-image"
            src={fileUrl(
              surveyId,
              previewFile.responseId,
              questionId,
              previewFile.index,
              true
            )}
            alt={previewFile.name}
          />
          <div className="responses-statistics-files-preview-footer">
            <Button
              variant="primary"
              className="responses-statistics-files-preview-download"
              href={fileUrl(
                surveyId,
                previewFile.responseId,
                questionId,
                previewFile.index
              )}
            >
              <i className="ri-download-line"></i> {t('Download')}
            </Button>
          </div>
        </div>
      )}
    </StatisticsDetailModal>
  )

  const searchBlock = (
    <div className="responses-statistics-array-text-search">
      <SearchInput
        terms={terms}
        onChange={setTerms}
        onTyping={setTyped}
        placeholder={t('Search responses')}
      />
      {search.length > 0 && totalResults != null && (
        <span className="responses-statistics-search-results">
          {totalResults === 1
            ? t('1 result found')
            : format(t('%s results found'), totalResults)}
        </span>
      )}
    </div>
  )

  const emptyState = (
    <div className="responses-statistics-empty">
      {search.length
        ? t('No responses match your search.')
        : t('There are no responses for this question yet.')}
    </div>
  )

  const filesTable = (items) => (
    <div className="responses-statistics-files">
      <LSTable columns={tableColumns} data={items} />
    </div>
  )

  const openModal = () => {
    setShowModal(true)
    if (hasNextPage && !isFetchingNextPage) {
      fetchNextPage()
    }
  }

  const renderContent = () => {
    if (!shouldLoad || isLoading) {
      return (
        <div className="responses-statistics-comments-status">
          <span className="loader"></span>
        </div>
      )
    }

    if (!files.length) {
      return emptyState
    }

    return (
      <>
        {filesTable(files.slice(0, INLINE_LIMIT))}
        {(hasNextPage || files.length > INLINE_LIMIT) && (
          <div className="responses-statistics-comments-more">
            <button
              type="button"
              className="responses-statistics-comments-more-btn"
              onClick={openModal}
            >
              {t('Load more')}
            </button>
          </div>
        )}
      </>
    )
  }

  return (
    <div ref={containerRef}>
      {shouldLoad && searchBlock}
      {renderContent()}
      {previewModal}
      <StatisticsDetailModal
        show={showModal}
        onHide={() => setShowModal(false)}
        modalClassname="responses-statistics-responses-modal"
      >
        <div className="responses-statistics-comments">
          <h2 className="responses-statistics-modal-title">
            {htmlToPlainText(title)}
          </h2>
          {searchBlock}
          {files.length ? filesTable(files) : emptyState}
          {hasNextPage && (
            <div className="responses-statistics-comments-more">
              <button
                type="button"
                className="responses-statistics-comments-more-btn"
                onClick={() => fetchNextPage()}
                disabled={isFetchingNextPage}
              >
                {isFetchingNextPage ? t('Loading...') : t('Load more')}
              </button>
            </div>
          )}
        </div>
      </StatisticsDetailModal>
    </div>
  )
}
