export const FeedbackSurveyModal = ({ surveyId, urlParams }) => {
  // Each cloud domain has as 'sur' subdomain which points to survey.limesurvey.org
  // - this allows us to avoid cross site cookie issues, so the session and CSRF
  // - cookies inside the frame are always on the same domain as the calling site
  // The survey will not work on custom domains which do not have this subdomain
  const feebackSubdomain = 'sur'
  // eslint-disable-next-line no-useless-escape
  const domainReplaceRegex = /^[^\/]*\.([^\/]+\.[^\/]+)/
  const feebackDomain = window.location.hostname.replace(
    domainReplaceRegex,
    feebackSubdomain + '.$1'
  )
  const feebackUrl =
    'https://' + feebackDomain + '/' + surveyId + '?' + urlParams.toString()

  return (
    <div className="feedback-container">
      <div className="feedback-title mt-5 mb-3">
        {t('Thanks for helping us improve!')}
      </div>
      <div className="feedback-description mb-4">
        {t(
          'Your feedback is important to us. Please take a minute to answer two quick questions.'
        )}
      </div>
      <div className="feedback-cover" />
      <iframe
        className="survey"
        src={feebackUrl}
        title={t('Feedback survey')}
      ></iframe>
    </div>
  )
}
