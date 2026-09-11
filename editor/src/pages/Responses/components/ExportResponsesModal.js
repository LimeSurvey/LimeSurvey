import { useEffect, useState } from 'react'
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
  exportRef,
}) => {
  const [exportOptions, setExportOptions] = useState({
    responseType: 'filtered',
    type: 'csv',
    language: surveyLanguage,
    answerFormat: 'long',
    csvSeparator: ',',
  })

  useEffect(() => {
    if (!exportRef) {
      return undefined
    }

    exportRef.current = {
      surveyId,
      options: exportOptions,
    }

    return () => {
      exportRef.current = null
    }
  }, [exportOptions, exportRef, surveyId])

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
