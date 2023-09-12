import { useEffect, useRef, useState } from 'react'
import { Button, OverlayTrigger } from 'react-bootstrap'
import { XLg, QrCodeScan } from 'react-bootstrap-icons'
import copy from 'copy-text-to-clipboard'
import QRCode from 'react-qr-code'
import classNames from 'classnames'

import {
  ConfirmAlert,
  GetInvalidSurveyObjects,
  HtmlPopup,
  Toast,
} from 'helpers'
import { useFocused } from 'hooks'
import { Input } from 'components'
import { ReactComponent as CopyIcon } from 'assets/icons/copy-icon.svg'
import { ReactComponent as SettingsIcon } from 'assets/icons/setttings-icon.svg'
import { CheckIcon, ShutDownIcon } from 'components/icons'

export const PublishSettings = ({
  isActivated = false,
  survey,
  update = () => {},
}) => {
  const [showPublishMenu, setShowPublishMenu] = useState(false)
  const [editingLink, setEditingLink] = useState(false)
  const linkRef = useRef(null)
  const qrCodeRef = useRef(null)

  const { setFocused } = useFocused()
  const [link, setLink] = useState('34567io')

  useEffect(() => {
    if (editingLink && linkRef.current) {
      linkRef.current.focus()
    }
  }, [editingLink])

  const linkOffset = 'http://limesurvey.net/'

  const handleOnCopyButtonClick = () => {
    copy(linkOffset + link)
  }

  const handleLinkChange = (value) => {
    setLink(value)
  }

  const handleEditingLink = () => {
    if (!link.trim()) {
      Toast('Link cannot be empty.')
      return
    }

    setEditingLink(!editingLink)
  }

  function convertSvgToPngAndDownload() {
    const svgString = new XMLSerializer().serializeToString(qrCodeRef.current)
    const canvas = document.createElement('canvas')
    const ctx = canvas.getContext('2d')

    const img = new Image()
    img.onload = function () {
      canvas.width = img.width
      canvas.height = img.height
      ctx.drawImage(img, 0, 0)

      const link = document.createElement('a')
      link.setAttribute('href', canvas.toDataURL('image/png'))
      link.setAttribute('download', 'QRCode.png')
      link.click()
    }

    img.src =
      'data:image/svg+xml;base64,' +
      btoa(unescape(encodeURIComponent(svgString)))
  }

  const QRCodePopup = (
    <div>
      <h3 className="text-start mt-2">Get the QR code</h3>
      <h6 className="text-start">
        Integrate the QR in your print or online media, to get more participants
      </h6>
      <QRCode
        size={256}
        title="Survey link"
        level="M"
        value={link}
        className="mb-3 qr-code"
        onChange={(e) => console.log(e)}
        ref={qrCodeRef}
      />
      <div className="text-end">
        <Button variant="outline-dark" onClick={convertSvgToPngAndDownload}>
          Download QR Code
        </Button>
      </div>
    </div>
  )

  const handleGeneratingQRCode = () => {
    HtmlPopup({
      html: QRCodePopup,
      showConfirmButton: false,
      showCancelButton: false,
      showCloseButton: true,
    })
  }

  const menu = (
    <div className="publish-settings-popover">
      <div className="p-3 pb-0 header fw-bold d-flex align-items-center justify-content-between">
        <p>Publish</p>
        <p>
          <XLg
            onClick={() => setShowPublishMenu(false)}
            cursor={'pointer'}
            stroke={'black'}
            fontWeight={800}
            color="black"
            size={15}
          />
        </p>
      </div>
      <div className="px-3 pt-2">
        <p className="public-link-header mb-2">PUBLIC LINK</p>
        <div className="mb-2">
          {editingLink && (
            <Input
              inputRef={linkRef}
              disabled={!editingLink}
              placeholder="Link"
              value={link}
              paddinRight="60px"
              onChange={({ target: { value } }) => handleLinkChange(value)}
              leftIcons={
                <span
                  className={classNames('limesurvey-link', {
                    disabled: editingLink,
                  })}
                >
                  {linkOffset}
                </span>
              }
              paddingLeft="166px"
              Icon={
                <>
                  <Button
                    variant="light"
                    className="copy-button me-1"
                    onClick={handleEditingLink}
                  >
                    <SettingsIcon />
                  </Button>
                  <Button
                    variant="light"
                    className="settings-button"
                    onClick={handleOnCopyButtonClick}
                  >
                    <CopyIcon />
                  </Button>
                </>
              }
            />
          )}
          {!editingLink && (
            <Input
              inputRef={linkRef}
              disabled={!editingLink}
              placeholder="Link"
              value={linkOffset + link}
              paddinRight="60px"
              onChange={({ target: { value } }) => handleLinkChange(value)}
              paddingLeft="166px"
              Icon={
                <>
                  <Button
                    variant="light"
                    className="copy-button me-1"
                    onClick={handleEditingLink}
                  >
                    <SettingsIcon />
                  </Button>
                  <Button
                    variant="light"
                    className="settings-button"
                    onClick={handleOnCopyButtonClick}
                  >
                    <CopyIcon />
                  </Button>
                </>
              }
            />
          )}
        </div>
        <div className="mt-2 mb-3">
          <Button
            variant="light"
            className="d-flex justify-content-between align-items-center"
            onClick={handleGeneratingQRCode}
          >
            <p className="mb-0">Generate QR Code</p>
            <QrCodeScan fontWeight={800} size={16} />
          </Button>
        </div>
      </div>
    </div>
  )

  const togglePublish = (isActivated) => {
    const title = isActivated ? '' : 'You are about to unpublish the survey.'
    const confirmButtonText = isActivated ? 'Yes, activate' : 'Yes, Unpublish'

    const publishInfo = `
      <div class="text-start">
        <h2 style="font-weight: 500; margin-bottom: 32px">
          Activate survey 
        </h2>
        <div style="font-weight: 500; line-height: 18px; margin-bottom: 28px">
          Please keep in mind: <br />
          Once a survey has been activated you can no longer add or delete questions, question groups, or subquestions.
        </div>
        <div>
          Editing questions, question groups, and subquestions is still possible.
        </div>
      </div>
    `

    const unpublishInfo = `
      The following will happen after unpublishing:<br/><br/>
      <div class="text-start">
        - No responses are lost. <br />
        - No participant information is lost. <br />
        - The ability to change questions, groups and parameters is limited. <br />
        - An expired survey cannot be accessed by participants. A message will be displayed stating that the survey has expired. <br />
        - It is still possible to perform statistical analysis on responses.
      </div>
    `

    if (!isActivated) {
      ConfirmAlert({
        title,
        confirmButtonText,
        html: isActivated ? publishInfo : unpublishInfo,
        width: 700,
        confirmButtonClass: 'swal-confirm-button-survey-activate',
        containerClass: 'publish-settings-swal-container',
        closeButtonClass: 'swal2-close',
      }).then((value) => {
        if (value.isConfirmed) {
          update({ isActivated })
          setShowPublishMenu(isActivated)
        }
      })

      return
    }

    const invalidSurveyObjects = GetInvalidSurveyObjects(survey)

    const groupKeys = Object.keys(
      invalidSurveyObjects?.gid ? invalidSurveyObjects?.gid : {}
    )
    const questionKeys = Object.keys(
      invalidSurveyObjects?.qid ? invalidSurveyObjects?.qid : {}
    )

    if (groupKeys.length) {
      const firstGroup = invalidSurveyObjects.gid[groupKeys[0]]
      setFocused(
        {
          ...survey.questionGroups[firstGroup.groupIndex],
        },
        firstGroup.groupIndex
      )
    }

    if (questionKeys.length) {
      const firstQuestion = invalidSurveyObjects.qid[questionKeys[0]]
      setFocused(
        {
          ...survey.questionGroups[firstQuestion.groupIndex].questions[
            firstQuestion.questionIndex
          ],
        },
        firstQuestion.groupIndex,
        firstQuestion.questionIndex
      )
    }

    if (questionKeys.length || groupKeys.length) {
      Toast(
        'Invalid survey questions. Please resolve the errors and try again.'
      )
      return
    }

    ConfirmAlert({
      title,
      confirmButtonText,
      html: isActivated ? publishInfo : unpublishInfo,
      width: 700,
      confirmButtonClass: 'swal-confirm-button-survey-activate',
      containerClass: 'publish-settings-swal-container',
      closeButtonClass: 'swal2-close',
    }).then((value) => {
      if (value.isConfirmed) {
        update({ isActivated })
        setShowPublishMenu(isActivated)
      }
    })
  }

  return (
    <>
      <OverlayTrigger
        trigger="click"
        overlay={menu}
        placement="bottom"
        show={showPublishMenu}
        onToggle={(show) => {
          if (!show) {
            setShowPublishMenu(show)
            setEditingLink(show)
          }
        }}
        offset={[0, 10]}
        rootClose
      >
        {isActivated ? (
          <Button
            variant="danger"
            className="published-button align-items-center d-flex ml-auto m-1"
            onClick={() => {
              togglePublish(false)
              setShowPublishMenu(false)
            }}
          >
            <div
              className="d-flex align-items-center"
              style={{ width: '20px' }}
            >
              <ShutDownIcon className={`fill-current text-white me-1`} />
            </div>
            <p className="m-0 text-white">Stop</p>
          </Button>
        ) : (
          <Button
            variant="success"
            className="publish-button d-flex align-items-center ml-auto m-1"
            onClick={() => togglePublish(true)}
          >
            <div
              style={{ width: '20px' }}
              className="d-flex align-items-center"
            >
              <CheckIcon className={`fill-current text-white me-1`} />
            </div>
            <p className="m-0 text-white">Activate</p>
          </Button>
        )}
      </OverlayTrigger>
    </>
  )
}
