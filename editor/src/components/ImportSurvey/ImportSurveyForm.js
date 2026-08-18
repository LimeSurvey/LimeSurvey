import { Alert, Form } from 'react-bootstrap'

import { FileDropZone, FormCheck, Select } from 'components/UIComponents'
import {
  getImportSurveyGroupOptions,
  IMPORT_SURVEY_FILE_TYPES,
  IMPORT_SURVEY_GROUP_STRATEGIES,
} from './importSurveyConfig'

export const ImportSurveyForm = ({
  convertResourceLinks,
  file,
  fileError,
  groupStrategy,
  maxFileSize,
  onConvertResourceLinksChange,
  onFileChange,
  onFileReject,
  onGroupStrategyChange,
  submitting,
}) => {
  const surveyGroupOptions = getImportSurveyGroupOptions()

  return (
    <Form id="import-survey-form">
      <div className="import-survey-modal__description">
        <p className="mb-1">
          {t(
            'Select a survey structure file (.lss, .txt, .tsv) or survey archive file (.lsa)'
          )}
        </p>
        <p className="mb-0">
          {t('Maximum file size {{size}} MB', {
            size: (maxFileSize / 1024 / 1024).toFixed(2),
          })}
        </p>
      </div>
      <FileDropZone
        accept={IMPORT_SURVEY_FILE_TYPES}
        disabled={submitting}
        error={fileError}
        file={file}
        id="import-survey-file"
        label={t('Select or drop a file here')}
        maxSize={maxFileSize}
        onChange={onFileChange}
        onReject={onFileReject}
      />
      <Select
        className="import-survey-modal__group-select"
        disabled={submitting}
        labelText={t('Destination survey group:')}
        onChange={onGroupStrategyChange}
        options={surveyGroupOptions}
        value={groupStrategy}
      />
      {groupStrategy === IMPORT_SURVEY_GROUP_STRATEGIES.FROM_SURVEY && (
        <Alert className="mt-2 mb-0" variant="warning">
          {t(
            'Survey group will be matched by name. Please note that survey group permissions will be inherited by the imported survey.'
          )}
        </Alert>
      )}
      <FormCheck
        checked={convertResourceLinks}
        className="mt-3"
        id="import-survey-convert-links"
        label={t('Convert resource links and expression fields?')}
        noAccessDisabled={submitting}
        type="checkbox"
        update={onConvertResourceLinksChange}
      />
    </Form>
  )
}
