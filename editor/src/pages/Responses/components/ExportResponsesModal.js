import { useEffect, useState } from 'react'
import { getSiteUrl } from 'helpers'

// Reuses the classic admin export form markup/logic (format, CSV separator,
// export data scope, language) instead of duplicating it in React.
export const ExportResponsesModal = ({ surveyId, formRef }) => {
  const [html, setHtml] = useState(null)
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    let isMounted = true
    setIsLoading(true)

    fetch(
      getSiteUrl(`/admin/export/sa/exportresults/surveyid/${surveyId}?modal=1`),
      { credentials: 'include', cache: 'no-store' }
    )
      .then((response) => response.text())
      .then((text) => {
        if (isMounted) setHtml(text)
      })
      .finally(() => {
        if (isMounted) setIsLoading(false)
      })

    return () => {
      isMounted = false
    }
  }, [surveyId])

  if (isLoading) {
    return (
      <div className="responses-export text-center p-4">
        <div className="spinner-border text-primary"></div>
      </div>
    )
  }

  return (
    <div
      className="responses-export"
      ref={formRef}
      dangerouslySetInnerHTML={{ __html: html }}
    />
  )
}
