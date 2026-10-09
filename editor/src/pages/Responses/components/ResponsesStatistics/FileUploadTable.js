import { useEffect, useMemo, useState } from 'react'
import { format } from 'util'

import {
  Button,
  HighlightedText,
  LSTable,
  SearchInput,
  TooltipContainer,
  useSearchTerms,
} from 'components'
import { getSiteUrl, htmlToPlainText, STATES } from 'helpers'
import { useAppState, useQuestionFiles } from 'hooks'
import { useIsInViewport } from 'hooks/useInViewport'

import { StatisticsDetailModal } from './StatisticsDetailModal.js'

const FILES_PER_PAGE = 5

const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp']

const FILE_ICONS = {
  'ri-image-line': ['svg', 'tif', 'tiff', 'heic', 'ico'],
  'ri-file-pdf-2-line': ['pdf'],
  'ri-file-word-line': ['doc', 'docx', 'odt', 'rtf'],
  'ri-file-excel-line': ['xls', 'xlsx', 'ods', 'csv'],
  'ri-file-ppt-line': ['ppt', 'pptx', 'odp'],
  'ri-file-zip-line': ['zip', 'rar', '7z', 'gz', 'tar'],
  'ri-file-music-line': ['mp3', 'wav', 'ogg', 'm4a', 'flac'],
  'ri-file-video-line': ['mp4', 'mov', 'avi', 'webm', 'mkv'],
  'ri-file-text-line': ['txt', 'md'],
}

const fileIcon = (extension) =>
  Object.keys(FILE_ICONS).find((icon) =>
    FILE_ICONS[icon].includes(extension)
  ) ?? 'ri-file-line'

const formatNumber = (value, locale) => {
  const options = { maximumFractionDigits: 1 }
  try {
    return value.toLocaleString(locale, options)
  } catch {
    return value.toLocaleString(undefined, options)
  }
}

// Sizes are stored in kilobytes by the uploader.
const formatFileSize = (sizeKb, locale) => {
  const kb = Number(sizeKb) || 0
  if (kb < 1) {
    return `${formatNumber(Math.round(kb * 1024), locale)} ${t('B')}`
  }
  if (kb < 1024) {
    return `${formatNumber(kb, locale)} ${t('KB')}`
  }
  return `${formatNumber(kb / 1024, locale)} ${t('MB')}`
}

const fileUrl = (surveyId, responseId, questionId, index, inline = false) =>
  getSiteUrl(
    `/responses/downloadfile?surveyId=${surveyId}&responseId=${responseId}&qid=${questionId}&index=${index}${inline ? '&inline=1' : ''}`
  )

const FileIcon = ({ extension }) => (
  <span className="responses-statistics-files-thumb responses-statistics-files-thumb--icon">
    <i className={fileIcon(extension)}></i>
  </span>
)

const FileThumbnail = ({ file, previewUrl }) => {
  const [failed, setFailed] = useState(false)
  if (!file.isImage || failed) {
    return <FileIcon extension={file.extension} />
  }
  return (
    <span className="responses-statistics-files-thumb">
      <img
        src={previewUrl}
        alt={file.name}
        loading="lazy"
        onError={() => setFailed(true)}
      />
    </span>
  )
}

const PreviewMedia = ({ file, src }) => {
  const [failed, setFailed] = useState(!file.isImage)
  if (failed) {
    return (
      <div className="responses-statistics-files-preview-fallback">
        <i className={fileIcon(file.extension)}></i>
        <span>{t('Preview not available for this file.')}</span>
      </div>
    )
  }
  return (
    <div className="responses-statistics-files-preview-media">
      <img src={src} alt={file.name} onError={() => setFailed(true)} />
    </div>
  )
}

/**
 * Uploaded files of a file upload question (|): one table row per file, with
 * preview and download actions. Search and pagination run per file in the
 * backend.
 */
export const FileUploadTable = ({
  surveyId,
  questionId,
  questionCode,
  fields,
  filters,
}) => {
  const [containerRef, isInView] = useIsInViewport(null, {
    initialInView: false,
  })
  const [shouldLoad, setShouldLoad] = useState(false)
  const [previewFile, setPreviewFile] = useState(null)
  const [userDetail] = useAppState(STATES.USER_DETAIL)
  const locale = userDetail?.lang === 'auto' ? undefined : userDetail?.lang
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

  // The numeric Cfilecount column sits next to the JSON column holding the files.
  const fileField = (fields ?? []).find((field) => !field.endsWith('filecount'))

  const {
    files: fileItems,
    totalResults,
    isLoading,
    hasNextPage,
    fetchNextPage,
    isFetchingNextPage,
  } = useQuestionFiles(surveyId, questionCode, {
    enabled: shouldLoad,
    field: fileField,
    filters,
    search,
    pageSize: FILES_PER_PAGE,
  })

  const files = useMemo(
    () =>
      fileItems.map((file) => ({
        ...file,
        id: `${file.responseId}-${file.index}`,
        extension: file.ext,
        isImage: IMAGE_EXTENSIONS.includes(file.ext),
      })),
    [fileItems]
  )

  const tableColumns = useMemo(
    () => [
      {
        id: 'file',
        header: t('File'),
        cell: ({ row }) => {
          const file = row.original
          const content = (
            <>
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
              <TooltipContainer tip={file.name}>
                <span className="responses-statistics-files-name">
                  <HighlightedText text={file.name} terms={highlightTerms} />
                </span>
              </TooltipContainer>
            </>
          )
          if (file.isImage) {
            return (
              <button
                type="button"
                className="responses-statistics-files-file responses-statistics-files-file--clickable"
                onClick={() => setPreviewFile(file)}
                aria-label={t('Preview file')}
              >
                {content}
              </button>
            )
          }
          return (
            <div className="responses-statistics-files-file">{content}</div>
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
        cell: ({ row }) => formatFileSize(row.original.size, locale),
      },
      {
        id: 'actions',
        header: t('Actions'),
        cell: ({ row }) => {
          const file = row.original
          return (
            <div className="responses-statistics-files-actions">
              {file.isImage && (
                <TooltipContainer tip={t('Preview file')}>
                  <button
                    type="button"
                    onClick={() => setPreviewFile(file)}
                    aria-label={t('Preview file')}
                  >
                    <i className="ri-eye-line"></i>
                  </button>
                </TooltipContainer>
              )}
              <TooltipContainer tip={t('Download file')}>
                <a
                  href={fileUrl(
                    surveyId,
                    file.responseId,
                    questionId,
                    file.index
                  )}
                  aria-label={t('Download file')}
                >
                  <i className="ri-download-line"></i>
                </a>
              </TooltipContainer>
            </div>
          )
        },
      },
    ],
    [surveyId, questionId, highlightTerms, locale]
  )

  const previewTitle = htmlToPlainText(previewFile?.title ?? '')

  const previewModal = (
    <StatisticsDetailModal
      show={previewFile !== null}
      onHide={() => setPreviewFile(null)}
      modalClassname="responses-statistics-files-preview"
    >
      {previewFile && (
        <div className="responses-statistics-files-preview-body">
          <h2 className="responses-statistics-modal-title">
            {previewTitle || previewFile.name}
          </h2>
          {previewTitle && (
            <span className="responses-statistics-files-preview-name">
              {previewFile.name}
            </span>
          )}
          {previewFile.comment && (
            <p className="responses-statistics-files-preview-comment">
              {htmlToPlainText(previewFile.comment)}
            </p>
          )}
          <PreviewMedia
            key={previewFile.id}
            file={previewFile}
            src={fileUrl(
              surveyId,
              previewFile.responseId,
              questionId,
              previewFile.index,
              true
            )}
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

  const renderContent = () => {
    if (!shouldLoad || isLoading) {
      return (
        <div className="responses-statistics-comments-status">
          <span className="loader"></span>
        </div>
      )
    }

    if (!files.length) {
      return (
        <div className="responses-statistics-empty">
          {search.length
            ? t('No responses match your search.')
            : t('There are no responses for this question yet.')}
        </div>
      )
    }

    return (
      <>
        <div className="responses-statistics-files">
          <LSTable columns={tableColumns} data={files} resizable />
        </div>
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
      </>
    )
  }

  return (
    <div ref={containerRef}>
      {shouldLoad && (
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
      )}
      {renderContent()}
      {previewModal}
    </div>
  )
}
