import { Alert, Table } from 'react-bootstrap'
import { decodeHtmlEntities } from './decodeHtmlEntities'

const SUMMARY_FIELDS = [
  ['surveys', 'Surveys'],
  ['languages', 'Languages'],
  ['groups', 'Question groups'],
  ['questions', 'Questions'],
  ['question_attributes', 'Question attributes'],
  ['answers', 'Answers'],
  ['subquestions', 'Subquestions'],
  ['defaultvalues', 'Default answers'],
  ['conditions', 'Conditions'],
  ['labelsets', 'Label sets'],
  ['assessments', 'Assessments'],
  ['quota', 'Quotas'],
  ['quotamembers', 'Quota rules'],
  ['quotals', 'Quota language settings'],
  ['plugin_settings', 'Plugin settings'],
  ['themes', 'Themes'],
  ['responses', 'Responses'],
]

export const ImportSurveySummary = ({ summary }) => {
  const rows = SUMMARY_FIELDS.filter(
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
              <th scope="row">{t(label)}</th>
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
