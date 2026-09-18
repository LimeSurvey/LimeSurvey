import classNames from 'classnames'

// Mirrors the "Show privacy policy text with mandatory checkbox" survey
// setting values (see application/models/Survey.php showsurveypolicynotice).
export const PRIVACY_POLICY_MODE = {
  OFF: 0,
  INLINE: 1,
  POPUP: 2,
}

// Static, read-only preview of how the privacy policy notice/checkbox is
// rendered on the actual survey welcome screen (see
// themes/survey/fruity_twentythree/views/subviews/privacy/privacy_text.twig
// and privacy_modal.twig). No modal or checkbox interaction is needed here,
// this only needs to visually reflect the survey rendering.
export const SurveyPrivacyPolicy = ({
  mode,
  policyNotice,
  policyNoticeLabel,
}) => {
  if (
    mode !== PRIVACY_POLICY_MODE.INLINE &&
    mode !== PRIVACY_POLICY_MODE.POPUP
  ) {
    return <></>
  }

  const isPopUp = mode === PRIVACY_POLICY_MODE.POPUP
  const checkboxLabel =
    policyNoticeLabel ||
    st('To continue please first accept our survey privacy policy.')

  return (
    <div
      className={classNames('privacy', {
        'survey-privacy-policy-inline': !isPopUp,
        'survey-privacy-policy-popup': isPopUp,
      })}
      data-testid="survey-privacy-policy"
    >
      {!isPopUp && (
        <div className="ls-privacy-block">
          <div
            className="datasecurity-notice-label fw-bold text-uppercase"
            data-testid="privacy-policy-notice-label"
          >
            {st('Privacy policy')}
          </div>
          <div
            className="datasecurity-notice"
            data-testid="privacy-policy-notice"
            dangerouslySetInnerHTML={{ __html: policyNotice }}
          />
        </div>
      )}
      <div className="ls-privacy-block">
        <div className="align-items-center datasecurity-checkbox-container">
          <div className="col-12 checkbox-item">
            <div className="datasecurity-checkbox-row d-flex align-items-center">
              <input
                disabled
                readOnly
                className="form-check-input"
                type="checkbox"
                tabIndex={-1}
              />
              <label className="control-label checkbox-label form-check-label datasecurity-checkbox-label">
                {checkboxLabel}
              </label>
            </div>
            {isPopUp && (
              <div className="show-policy-row">
                <span className="show-policy">{st('Show policy')}</span>
              </div>
            )}
          </div>
        </div>
      </div>
    </div>
  )
}
