import { Badge } from 'react-bootstrap'

export const FileInfoCell = ({ filesInfo, surveyId, rowId, questionId }) => {
  const filesToRender = filesInfo.map((fileInfo, index) => {
    // To avoid showing that the file size is 0 MB, incase of the size is very small.
    const fileSize = Math.max(fileInfo.approxFileSizeInMB, 0.1)

    if (fileInfo.isDeleted) {
      return (
        <Badge key={`${fileInfo.key}-${index}`}>
          <span className="file-name text-danger">
            {t('File has been deleted')}
          </span>
        </Badge>
      )
    }

    return (
      <div className="td-value-container" key={`${fileInfo.key}-${index}`}>
        <Badge>
          <div>
            <span className="file-name">{fileInfo.name}:</span>
            <a
              href={`${location.protocol}//${location.hostname}/responses/downloadfile?surveyId=${surveyId}&responseId=${rowId}&qid=${questionId}&index=${index}`}
              className="ms-1 file-size"
              target="_blank"
              rel="noreferrer"
            >
              {fileSize}
              {t('MB')}
            </a>
          </div>
        </Badge>
        {fileInfo.comment && (
          <Badge>
            <div>
              <span className="file-comment">
                {t('Comment')}: {fileInfo.comment}
              </span>
            </div>
          </Badge>
        )}
        {fileInfo.title && (
          <Badge>
            <div>
              <span className="file-title">
                {t('Title')}: {fileInfo.title}
              </span>
            </div>
          </Badge>
        )}
      </div>
    )
  })

  return filesToRender
}
