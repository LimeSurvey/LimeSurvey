import { Alert, Table } from 'react-bootstrap'
import { decodeHtmlEntities } from './decodeHtmlEntities'

const getSummaryFields = () => [
  ['surveys', t('Surveys')],
  ['languages', t('Languages')],
  ['groups', t('Question groups')],
  ['questions', t('Questions')],
  ['question_attributes', t('Question attributes')],
  ['answers', t('Answers')],
  ['subquestions', t('Subquestions')],
  ['defaultvalues', t('Default answers')],
  ['conditions', t('Conditions')],
  ['labelsets', t('Label sets')],
  ['assessments', t('Assessments')],
  ['quota', t('Quotas')],
  ['quotamembers', t('Quota rules')],
  ['quotals', t('Quota language settings')],
  ['plugin_settings', t('Plugin settings')],
  ['themes', t('Themes')],
  ['responses', t('Responses')],
]

export const ImportSurveySummary = ({ summary }) => {
  const rows = getSummaryFields().filter(
    ([key]) => summary[key] !== undefined && summary[key] !== null
  )

  return (
    <div className="import-survey-summary">
      <p>{t('Import of survey completed.')}</p>
      <Table bordered size="sm" className="mb-0">
        <caption className="visually-hidden">
          {t('Survey structure import summary')}
        </caption>
        <colgroup>
          <col className="import-survey-summary__label-column" />
          <col className="import-survey-summary__value-column" />
        </colgroup>
        <tbody>
          {rows.map(([key, label]) => (
            <tr key={key}>
              <th scope="row">{label}</th>
              <td className="text-end">{summary[key]}</td>
            </tr>
          ))}
        </tbody>
      </Table>
      {summary.importwarnings?.length > 0 && (
        <Alert className="mt-3 mb-0" variant="warning">
          <p className="fw-semibold mb-1">{t('Warnings')}</p>
          <ul className="mb-0 ps-3">
            {summary.importwarnings.map((warning, index) => (
              <li key={`${warning}-${index}`}>{decodeHtmlEntities(warning)}</li>
            ))}
          </ul>
        </Alert>
      )}
    </div>
  )
}
