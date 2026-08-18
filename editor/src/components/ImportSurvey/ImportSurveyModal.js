import { useEffect, useState } from 'react'
import { Modal, Spinner } from 'react-bootstrap'

import { Button } from 'components/UIComponents'
import { ImportSurveyForm } from './ImportSurveyForm'
import { ImportSurveySummary } from './ImportSurveySummary'
import {
  IMPORT_SURVEY_GROUP_STRATEGIES,
  IMPORT_SURVEY_MAX_FILE_SIZE,
} from './importSurveyConfig'
import { showImportError, showImportSuccess } from './importSurveyNotifications'

export const ImportSurveyModal = ({
  maxFileSize,
  onGoToSurvey = () => {},
  onHide = () => {},
  onImport = async () => null,
  show = false,
  summary: controlledSummary = null,
}) => {
  const effectiveMaxFileSize = maxFileSize || IMPORT_SURVEY_MAX_FILE_SIZE
  const [convertResourceLinks, setConvertResourceLinks] = useState(true)
  const [file, setFile] = useState(null)
  const [fileError, setFileError] = useState('')
  const [groupStrategy, setGroupStrategy] = useState(
    IMPORT_SURVEY_GROUP_STRATEGIES.DEFAULT
  )
  const [submitting, setSubmitting] = useState(false)
  const [submittedSummary, setSubmittedSummary] = useState(null)
  const summary = controlledSummary || submittedSummary

  useEffect(() => {
    if (!show) {
      setConvertResourceLinks(true)
      setFile(null)
      setFileError('')
      setGroupStrategy(IMPORT_SURVEY_GROUP_STRATEGIES.DEFAULT)
      setSubmittedSummary(null)
      setSubmitting(false)
    }
  }, [show])

  const handleFileChange = (nextFile) => {
    setFile(nextFile)
    setFileError('')
  }

  const handleFileReject = (rejections) => {
    const code = rejections[0]?.errors[0]?.code
    const message =
      code === 'file-too-large'
        ? t('The selected file is too large.')
        : t('Please select an .lss, .lsa, .txt, or .tsv file.')
    setFile(null)
    setFileError(message)
    showImportError(message)
  }

  const handleSubmit = async () => {
    if (!file) {
      const message = t('No file selected')
      setFileError(message)
      showImportError(message)
      return
    }

    setSubmitting(true)
    try {
      const result = await onImport({
        convertResourceLinks,
        file,
        groupStrategy,
      })
      if (!result?.newsid) {
        throw new Error(t('The import completed without a new survey ID.'))
      }
      setSubmittedSummary(result)
      showImportSuccess()
    } catch (importError) {
      showImportError(
        importError?.message || t('The survey could not be imported.')
      )
    } finally {
      setSubmitting(false)
    }
  }

  const handleGoToSurvey = () => {
    onGoToSurvey(summary?.newsid)
  }

  return (
    <Modal
      backdrop={submitting ? 'static' : true}
      centered
      className="import-survey-modal"
      keyboard={!submitting}
      onHide={submitting ? undefined : onHide}
      show={show}
    >
      <Modal.Header closeButton={!submitting}>
        <Modal.Title>
          {summary ? t('Import summary') : t('Import survey')}
        </Modal.Title>
      </Modal.Header>
      <Modal.Body>
        {summary ? (
          <ImportSurveySummary summary={summary} />
        ) : (
          <ImportSurveyForm
            convertResourceLinks={convertResourceLinks}
            file={file}
            fileError={fileError}
            groupStrategy={groupStrategy}
            maxFileSize={effectiveMaxFileSize}
            onConvertResourceLinksChange={setConvertResourceLinks}
            onFileChange={handleFileChange}
            onFileReject={handleFileReject}
            onGroupStrategyChange={({ value }) => setGroupStrategy(value)}
            submitting={submitting}
          />
        )}
      </Modal.Body>
      <Modal.Footer>
        {summary ? (
          <>
            <Button onClick={onHide} variant="secondary">
              {t('Close')}
            </Button>
            <Button
              className="text-light"
              disabled={!summary.newsid}
              onClick={handleGoToSurvey}
              variant="primary"
            >
              {t('Go to survey')}
            </Button>
          </>
        ) : (
          <>
            <Button disabled={submitting} onClick={onHide} variant="secondary">
              {t('Cancel')}
            </Button>
            <Button
              className="text-light"
              disabled={!file || submitting}
              onClick={handleSubmit}
              variant="primary"
            >
              {submitting && (
                <Spinner
                  animation="border"
                  aria-hidden="true"
                  className="me-2"
                  size="sm"
                />
              )}
              {t('Import survey')}
            </Button>
          </>
        )}
      </Modal.Footer>
    </Modal>
  )
}
