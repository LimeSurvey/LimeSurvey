import { useEffect, useState } from 'react'
import { Form } from 'react-bootstrap'
import { useTranslation } from 'react-i18next'
import { decodeHTMLEntities, STATES } from 'helpers'
import { useAppState } from 'hooks'

/**
 * Export options form component for REST API-based export.
 * Handles format selection, language, and answer format.
 */
export const ExportOptionsForm = ({
  surveyLanguage,
  additionalLanguages = '',
  onOptionsChange,
}) => {
  const { t } = useTranslation()
  const [allLanguages] = useAppState(STATES.ALL_AVAILABLE_LANGUAGES)
  const [userDetails] = useAppState(STATES.USER_DETAIL)
  const languageNames = allLanguages?.[userDetails?.lang]
  const [responseType, setResponseType] = useState('filtered')
  const [type, setType] = useState('csv')
  const [language, setLanguage] = useState(surveyLanguage)
  const [answerFormat] = useState('long')
  const [csvSeparator, setCsvSeparator] = useState(',')

  // Build language options from survey language + additional languages
  const languages = surveyLanguage ? [surveyLanguage] : []
  if (additionalLanguages && typeof additionalLanguages === 'string') {
    const additional = additionalLanguages
      .split(' ')
      .filter((l) => l && l !== surveyLanguage)
    languages.push(...additional)
  }

  const responseTypeOptions = [
    { label: t('Filtered data'), value: 'filtered' },
    { label: t('All data'), value: 'all' },
  ]

  // All available export formats, grouped into rows matching the design.
  const allExportFormats = [
    { value: 'csv', label: t('CSV') },
    { value: 'html', label: t('HTML') },
    { value: 'pdf', label: t('PDF') },
    { value: 'spss', label: t('SPSS (sav.)') },
    { value: 'stata', label: t('STATA (.xml)') },
    { value: 'r_syntax', label: t('R (syntax)') },
    { value: 'r_data', label: t('R (data file)') },
    { value: 'json', label: t('JSON') },
    { value: 'excel', label: t('Microsoft Excel') },
    { value: 'word', label: t('Microsoft Word') },
  ]
  const formatRows = [
    ['csv', 'html'],
    ['pdf', 'spss', 'stata', 'r_syntax'],
    ['r_data', 'json', 'excel', 'word'],
  ]

  const csvSeparatorOptions = [
    { label: t('Comma (,)'), value: ',' },
    { label: t('Semicolon (;)'), value: ';' },
    { label: t('Tab'), value: '\t' },
  ]

  // Notify parent of form state changes
  useEffect(() => {
    if (onOptionsChange) {
      onOptionsChange({
        responseType,
        type,
        language,
        answerFormat,
        csvSeparator,
      })
    }
  }, [
    responseType,
    type,
    language,
    answerFormat,
    csvSeparator,
    onOptionsChange,
  ])

  return (
    <div className="export-options-form">
      <div className="export-options-section">
        <label className="export-options-label">{t('Export data')}</label>
        <div className="export-format-options">
          {responseTypeOptions.map((option) => (
            <div
              key={option.value}
              className="export-format-option"
              data-fenceable="export-data"
              data-fence-key={option.value}
            >
              <Form.Check
                type="radio"
                id={`response-type-${option.value}`}
                name="responseType"
                value={option.value}
                label={option.label}
                checked={responseType === option.value}
                onChange={(e) => setResponseType(e.target.value)}
              />
            </div>
          ))}
        </div>
      </div>

      <div className="export-options-section">
        <label className="export-options-label">{t('File formats')}</label>
        <div className="export-format-grid">
          {formatRows.map((row, rowIndex) => (
            <div key={rowIndex} className="export-format-row">
              {row.map((value) => {
                const format = allExportFormats.find(
                  (item) => item.value === value
                )
                return (
                  <div
                    key={format.value}
                    className="export-format-option"
                    data-fenceable="export-format"
                    data-fence-key={format.value}
                  >
                    <Form.Check
                      type="radio"
                      id={`format-${format.value}`}
                      name="exportFormat"
                      value={format.value}
                      label={format.label}
                      checked={type === format.value}
                      onChange={(e) => setType(e.target.value)}
                    />
                  </div>
                )
              })}
            </div>
          ))}
        </div>
      </div>

      {type === 'csv' && (
        <div className="export-options-section">
          <label className="export-options-label">
            {t('CSV file separator')}
          </label>
          <div className="export-format-options">
            {csvSeparatorOptions.map((option) => (
              <div
                key={option.value}
                className="export-format-option"
                data-fenceable="csv-separator"
                data-fence-key={option.value}
              >
                <Form.Check
                  type="radio"
                  id={`csv-separator-${option.value}`}
                  name="csvSeparator"
                  value={option.value}
                  label={option.label}
                  checked={csvSeparator === option.value}
                  onChange={(e) => setCsvSeparator(e.target.value)}
                />
              </div>
            ))}
          </div>
        </div>
      )}

      {languages.length > 1 && (
        <div className="export-options-section">
          <label className="export-options-label">{t('Export language')}</label>
          <div className="export-format-options">
            {languages.map((lang) => (
              <div
                key={lang}
                className="export-format-option"
                data-fenceable="export-language"
                data-fence-key={lang}
              >
                <Form.Check
                  type="radio"
                  id={`language-${lang}`}
                  name="exportLanguage"
                  value={lang}
                  label={decodeHTMLEntities(
                    languageNames?.[lang]?.description || lang
                  )}
                  checked={language === lang}
                  onChange={(e) => setLanguage(e.target.value)}
                />
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
