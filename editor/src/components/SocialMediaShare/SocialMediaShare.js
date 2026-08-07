import {
  FacebookShareButton,
  LinkedinShareButton,
  EmailShareButton,
  EmailIcon,
  TwitterShareButton,
} from 'react-share'

import facebookIcon from 'assets/icons/facebook-icon.svg'
import linkedinIcon from 'assets/icons/linkedin-icon.svg'
import xIcon from 'assets/icons/twitter-icon.svg'

export const SocialMediaShare = ({ shareUrl = '', surveyTitle = '' }) => {
  return (
    <>
      <h5 className="med16-c">{t('Share on social media')}</h5>
      <div className="d-flex flex-grow-1 flex-wrap justify-content-start align-content-center gap-3 m-auto">
        <FacebookShareButton url={shareUrl} title={surveyTitle}>
          <img src={facebookIcon} alt="bold icon" />
        </FacebookShareButton>
        <EmailShareButton
          subject={surveyTitle}
          url={shareUrl}
          title={surveyTitle}
        >
          <EmailIcon
            style={{ width: 36, height: 36, border: 5, borderRadius: 2 }}
            bgStyle={{ fill: '#6e748c' }}
          />
        </EmailShareButton>
        <LinkedinShareButton url={shareUrl} title={surveyTitle}>
          <img src={linkedinIcon} alt="bold icon" />
        </LinkedinShareButton>
        <TwitterShareButton url={shareUrl} title={surveyTitle}>
          <img src={xIcon} alt="x icon" width={42} height={42} />
        </TwitterShareButton>
      </div>
    </>
  )
}
