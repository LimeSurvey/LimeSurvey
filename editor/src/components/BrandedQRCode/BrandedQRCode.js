import React, { useRef } from 'react'
import { QRCode } from 'react-qrcode-logo'

import downloadArrowIcon from 'assets/icons/download-arrow.svg'
import lsIcon from 'assets/icons/limesurvey_logo.svg'
import { downloadCanvasAsImage, errorToast } from 'helpers'

export const BrandedQRCode = ({
  value = 'https://www.limesurvey.org/',
  size = 150,
  quietZone = 10,
  ecLevel = 'L',
  logoOpacity = 1,
  logoPaddingStyle = 'square',
  removeQrCodeBehindLogo = true,
  qrStyle = 'dots',
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

  const handleDownload = () => {
    if (!qrWrapperRef.current) return

    const canvas = qrWrapperRef.current.querySelector('canvas')
    if (canvas && typeof canvas.toDataURL === 'function') {
      downloadCanvasAsImage(canvas, 'QRCode.png')
    } else {
      errorToast(t('Failed to download QR code'), 'center')
    }
  }

  return (
    <>
      <div className="text-center mb-4" ref={qrWrapperRef}>
        <QRCode
          value={value}
          logoImage={lsIcon}
          size={size}
          quietZone={quietZone}
          ecLevel={ecLevel}
          logoOpacity={logoOpacity}
          removeQrCodeBehindLogo={removeQrCodeBehindLogo}
          qrStyle={qrStyle}
          eyeRadius={eyeRadius}
          eyeColor={eyeColor}
          fgColor={fgColor}
          bgColor={bgColor}
          logoPaddingStyle={logoPaddingStyle}
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
