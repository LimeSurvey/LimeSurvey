import { useEffect, useState } from 'react'
import { Form } from 'react-bootstrap'
import { useTranslation } from 'react-i18next'
import { decodeHTMLEntities, STATES } from 'helpers'
import { useAppState } from 'hooks'
import { UpgradeSparkleIcon } from 'components/icons'

/**
 * Export options form component for REST API-based export.
 * Handles format selection (with subscription gating), language, and answer format.
 * Displays upsell banner for free-tier users.
 */
export const ExportOptionsForm = ({
  surveyLanguage,
  additionalLanguages = '',
  isFreeUser = false,
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

  // All available export formats, grouped into rows matching the design
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

  // For MVP: Free tier only gets CSV + HTML; others get all (but only CSV/HTML routed to API for now)
  const allowedFormats = isFreeUser
    ? ['csv', 'html']
    : allExportFormats.map((f) => f.value)

  const getFormat = (value) => {
    const format = allExportFormats.find((f) => f.value === value)
    return { ...format, disabled: !allowedFormats.includes(value) }
  }

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
      {isFreeUser && (
        <div className="export-upsell-banner">
          <div className="export-upsell-banner-main">
            <div className="export-upsell-banner-heading">
              <span className="export-upsell-icon">
                <UpgradeSparkleIcon width={17} height={16} />
              </span>
              <span className="export-upsell-banner-title">
                {t('Take your results anywhere - unlock more formats')}
              </span>
            </div>
            <div className="export-upsell-banner-subtitle-row">
              <span className="export-upsell-banner-subtitle">
                {t(
                  'Switch to LimeSurvey Expert to receive the advanced export options.'
                )}
              </span>
            </div>
          </div>
          <span className="export-upsell-banner-button">
            {t('Show options')}
          </span>
        </div>
      )}

      <div className="export-options-section">
        <label className="export-options-label">{t('Export data')}</label>
        <div className="export-format-options">
          {responseTypeOptions.map((option) => (
            <div key={option.value} className="export-format-option">
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
        <label className="export-options-label">
          {t('File formats')}
          {isFreeUser && <UpgradeSparkleIcon className="export-sparkle" />}
        </label>
        <div className="export-format-grid">
          {formatRows.map((row, rowIndex) => (
            <div key={rowIndex} className="export-format-row">
              {row.map((value) => {
                const format = getFormat(value)
                return (
                  <div
                    key={format.value}
                    className={`export-format-option ${format.disabled ? 'export-format-option--fenced' : ''}`}
                  >
                    <Form.Check
                      type="radio"
                      id={`format-${format.value}`}
                      name="exportFormat"
                      value={format.value}
                      label={format.label}
                      checked={type === format.value && !format.disabled}
                      onChange={(e) =>
                        !format.disabled && setType(e.target.value)
                      }
                      disabled={format.disabled}
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
              <div key={option.value} className="export-format-option">
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
              <div key={lang} className="export-format-option">
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
