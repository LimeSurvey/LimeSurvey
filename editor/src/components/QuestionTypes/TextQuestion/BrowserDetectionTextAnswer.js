import React, { useEffect, useState } from 'react'
import { Form } from 'react-bootstrap'
import Bowser from 'bowser'
import {
  MapContainer,
  TileLayer,
  Popup,
  Marker as LeafLetMarker,
} from 'react-leaflet'
import L from 'leaflet'

import { ContentEditor } from 'components/UIComponents'
import { getAttributeValue } from 'helpers'

import './TextQuestion.scss'

const LeafletMapComponent = () => {
  const customIcon = new L.Icon({
    iconUrl: 'https://unpkg.com/leaflet@1.5.1/dist/images/marker-icon.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [0, -41],
  })

  return (
    <MapContainer
      center={[53.61422133647984, 9.972816890552014]}
      zoom={8}
      style={{ height: '350px', width: '100%' }}
    >
      <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
      <LeafLetMarker
        icon={customIcon}
        position={[53.61422133647984, 9.972816890552014]}
      >
        <Popup>{t('You are here!')}</Popup>
      </LeafLetMarker>
    </MapContainer>
  )
}

export const BrowserDetectionTextAnswer = ({ attributes = {} }) => {
  const [browserInfo, setBrowserInfo] = useState(false)
  const locationMapService = getAttributeValue(attributes.location_mapservice)

  useEffect(() => {
    const { browser, os } = Bowser.parse(window.navigator.userAgent)
    let info = ''
    if (attributes?.add_platform_info?.['']?.value === 'yes') {
      info = `${browser.name} (${browser.version}) | ${os.name} (${os.versionName})`
    } else {
      info = `${browser.name} (${browser.version})`
    }

    setBrowserInfo(info)
  }, [attributes?.add_platform_info])

  return (
    <div className={'question-body-content'}>
      {locationMapService === '0' && (
        <div className="d-flex gap-2 align-items-center justify-content-center">
          {attributes.prefix?.value && (
            <ContentEditor disabled={true} value={attributes.prefix?.value} />
          )}
          <Form.Group className="flex-grow-1">
            <Form.Control
              type={'text'}
              placeholder={st('Enter your answer here.')}
              data-testid="text-question-answer-input"
              value={browserInfo}
              disabled={true}
            />
          </Form.Group>
          {attributes.suffix && (
            <ContentEditor disabled={true} value={attributes.suffix?.value} />
          )}
        </div>
      )}
      {locationMapService === '1' && (
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2366.8538614773024!2d9.968793577861623!3d53.61390947236818!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47b1892bf70bdec9%3A0xee50470c12b1ae5e!2sLimeSurvey%20GmbH!5e0!3m2!1sen!2sde!4v1751636509270!5m2!1sen!2sde"
          width="100%"
          height="450"
          loading="lazy"
        ></iframe>
      )}
      {locationMapService === '100' && <LeafletMapComponent />}
    </div>
  )
}
