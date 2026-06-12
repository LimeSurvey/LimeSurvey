import React, { useRef } from 'react'
import { QRCode } from 'react-qrcode-logo'

import downloadArrowIcon from 'assets/icons/download-arrow.svg'
import lsIcon from 'assets/icons/limesurvey_logo.svg'
import { downloadCanvasAsImage, errorToast } from 'helpers'

export const BrandedQRCode = ({
  value = 'https://www.limesurvey.org/',
  previewSize = 150,
  previewPixelRatio = 3,
  previewQuietZone = 10,
  downloadTotal = 600,
  downloadQuietZone = 16,
  logoFraction = 0.2,
  ecLevel = 'Q',
  logoOpacity = 1,
  logoPadding = 4,
  logoPaddingStyle = 'square',
  removeQrCodeBehindLogo = true,
  qrStyle = 'fluid',
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
  const qrDownloadRef = useRef(null)

  const handleDownload = () => {
    const canvas = qrDownloadRef.current?.querySelector('canvas')
    if (!canvas) {
      errorToast(t('Failed to download QR code'), 'center')
      return
    }
    downloadCanvasAsImage(canvas, 'QRCode.png')
  }

  const previewRenderSize = previewSize * previewPixelRatio
  const downloadSize = downloadTotal - 2 * downloadQuietZone

  const sharedProps = {
    value,
    logoImage: lsIcon,
    ecLevel,
    logoOpacity,
    logoPadding,
    logoPaddingStyle,
    removeQrCodeBehindLogo,
    qrStyle,
    eyeRadius,
    eyeColor,
    fgColor,
    bgColor,
  }

  return (
    <>
      <div className="text-center mb-4">
        <QRCode
          size={previewRenderSize}
          quietZone={previewQuietZone * previewPixelRatio}
          logoWidth={previewRenderSize * logoFraction}
          logoHeight={previewRenderSize * logoFraction}
          style={{ width: previewSize, height: previewSize }}
          {...sharedProps}
        />
      </div>

      <div
        ref={qrDownloadRef}
        style={{ position: 'absolute', left: '-9999px', top: '-9999px' }}
      >
        <QRCode
          size={downloadSize}
          quietZone={downloadQuietZone}
          logoWidth={downloadSize * logoFraction}
          logoHeight={downloadSize * logoFraction}
          {...sharedProps}
        />
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
