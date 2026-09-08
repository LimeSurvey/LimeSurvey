import { useState } from 'react'
import { ExportOptionsForm } from 'components'

/**
 * Export responses modal component.
 * Displays format/language/options selection.
 * Export action is handled by parent component via ref.
 */
export const ExportResponsesModal = ({ 
  surveyId, 
  surveyLanguage, 
  additionalLanguages, 
  isFreeUser = false,
  exportRef
}) => {
  const [exportOptions, setExportOptions] = useState({
    type: 'csv',
    language: surveyLanguage,
    answerFormat: 'long',
    csvSeparator: ','
  })

  // Expose export options to parent via ref
  if (exportRef) {
    exportRef.current = {
      surveyId,
      options: exportOptions
    }
  }

  return (
    <div className="export-responses-modal">
      <ExportOptionsForm
        surveyLanguage={surveyLanguage}
        additionalLanguages={additionalLanguages}
        isFreeUser={isFreeUser}
        onOptionsChange={setExportOptions}
      />
    </div>
  )
}


