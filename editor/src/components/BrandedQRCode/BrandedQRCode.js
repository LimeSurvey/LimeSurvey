import React, { useRef } from 'react'
import { QRCode } from 'react-qrcode-logo'

import downloadArrowIcon from 'assets/icons/download-arrow.svg'
import lsIcon from 'assets/icons/limesurvey_logo.svg'
import { errorToast } from 'helpers'

export const BrandedQRCode = ({
  value = 'https://www.limesurvey.org/',
  size = 150,
  downloadSize = 350,
  quietZone = 10,
  ecLevel = 'Q',
  logoOpacity = 1,
  logoPaddingStyle = 'square',
  removeQrCodeBehindLogo = true,
  qrStyle = 'squares',
  eyeRadius = [
      { outer: [10, 10, 10, 10], inner: [0, 0, 0, 0] },
      { outer: [10, 10, 10, 10], inner: [0, 0, 0, 0] },
      { outer: [10, 10, 10, 10], inner: [0, 0, 0, 0] },
  ],
  eyeColor = {
      outer: '#000000',
      inner: '#25c267',
  },
  fgColor = '#000000',
  bgColor = '#ffffff',
  showDownloadButton = true,
}) => {
    const qrWrapperRef = useRef(null)
    const qrDownloadRef = useRef(null)

    const handleDownload = () => {
        if (qrDownloadRef.current) {
            qrDownloadRef.current.download('png', 'QRCode.png')
        } else {
            errorToast(t('Failed to download QR code'), 'center')
        }
    }

    const sharedProps = {
        value,
        logoImage: lsIcon,
        quietZone,
        ecLevel,
        logoOpacity,
        removeQrCodeBehindLogo,
        qrStyle,
        eyeRadius,
        eyeColor,
        fgColor,
        bgColor,
        logoPaddingStyle,
    }

    return (
        <>
            <div className="text-center mb-4" ref={qrWrapperRef}>
                <QRCode size={size} {...sharedProps} />
            </div>

            <div
                style={{ position: 'absolute', left: '-9999px', top: '-9999px' }}
            >
                <QRCode ref={qrDownloadRef} size={downloadSize} quietZone={quietZone * (downloadSize / size)} {...sharedProps} />
            </div>

            {showDownloadButton && (
                <div
                    className="med14-c text-primary cursor-pointer"
                    onClick={handleDownload}
                >
                    <img className="me-2" src={downloadArrowIcon} alt="Download QR" />
                    {t('Download QR code')}
                </div>
            )}
        </>
    )
}
