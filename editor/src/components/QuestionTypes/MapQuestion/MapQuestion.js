import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import {
  MapContainer,
  TileLayer,
  Popup,
  Marker as LeafLetMarker,
  useMap,
  useMapEvents,
} from 'react-leaflet'
import L from 'leaflet'

import { getAttributeValue } from 'helpers'

const DEFAULT_CENTER = [53.61422133647984, 9.972816890552014]
const DEFAULT_ZOOM = 11
const DEFAULT_HEIGHT = 300
const GOOGLE_MAPS_SERVICE = '1'

const attributeScalar = (attribute) => {
  const raw = getAttributeValue(attribute)
  if (raw && typeof raw === 'object' && 'value' in raw) {
    return raw.value
  }
  return raw
}

const parseLatLng = (value, separator) => {
  if (!value || typeof value !== 'string') {
    return null
  }

  const coords = value.split(separator).map((part) => Number(part.trim()))
  if (coords.length !== 2 || coords.some((n) => Number.isNaN(n))) {
    return null
  }

  return coords
}

const LeafletMapComponent = ({
  value,
  defaultCoordinates,
  zoom,
  height,
  onChange,
}) => {
  const customIcon = new L.Icon({
    iconUrl: 'https://unpkg.com/leaflet@1.5.1/dist/images/marker-icon.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [0, -41],
  })

  const initialPosition = useMemo(() => {
    return (
      parseLatLng(value, ';') ||
      parseLatLng(defaultCoordinates, ' ') ||
      DEFAULT_CENTER
    )
  }, [value, defaultCoordinates])

  const [position, setPosition] = useState(initialPosition)
  const markerRef = useRef(null)

  useEffect(() => {
    setPosition(initialPosition)
  }, [initialPosition])

  const updatePosition = useCallback(
    ({ lat, lng }) => {
      const newPosition = [lat, lng]
      setPosition(newPosition)
      onChange?.(`${lat};${lng}`)
    },
    [onChange]
  )

  const MapClickHandler = () => {
    useMapEvents({
      click: (event) => updatePosition(event.latlng),
    })
    return null
  }

  const MapViewSyncer = () => {
    const map = useMap()
    useEffect(() => {
      map.setView(position, zoom)
    }, [map, position, zoom])
    return null
  }

  const markerEventHandlers = useMemo(
    () => ({
      dragend: () => {
        const marker = markerRef.current
        if (marker) {
          updatePosition(marker.getLatLng())
        }
      },
    }),
    [updatePosition]
  )

  return (
    <MapContainer
      center={position}
      zoom={zoom}
      style={{ height: `${height}px`, width: '100%' }}
    >
      <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
      <MapClickHandler />
      <MapViewSyncer />
      <LeafLetMarker
        icon={customIcon}
        position={position}
        draggable={true}
        eventHandlers={markerEventHandlers}
        ref={markerRef}
      >
        <Popup>
          {t('Drag the marker or click the map to set the location')}
        </Popup>
      </LeafLetMarker>
    </MapContainer>
  )
}

export const MapQuestion = ({
  question: { attributes = {} } = {},
  values = [],
  onValueChange = () => {},
}) => {
  const value = values?.[0] || {}
  const locationMapService = String(
    attributeScalar(attributes.location_mapservice) ?? '100'
  )
  const zoom =
    Number(attributeScalar(attributes.location_mapzoom)) || DEFAULT_ZOOM
  const height =
    Number(attributeScalar(attributes.location_mapheight)) || DEFAULT_HEIGHT
  const defaultCoordinates = attributeScalar(
    attributes.location_defaultcoordinates
  )

  const handleOnChange = (newValue) => {
    onValueChange(newValue, value.key)
  }

  return (
    <div className="question-body-content" data-testid="map-question">
      {locationMapService === GOOGLE_MAPS_SERVICE ? (
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2366.8538614773024!2d9.968793577861623!3d53.61390947236818!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47b1892bf70bdec9%3A0xee50470c12b1ae5e!2sLimeSurvey%20GmbH!5e0!3m2!1sen!2sde!4v1751636509270!5m2!1sen!2sde"
          width="100%"
          height={height}
          loading="lazy"
          title={t('Map')}
        ></iframe>
      ) : (
        <LeafletMapComponent
          value={value.value}
          defaultCoordinates={defaultCoordinates}
          zoom={zoom}
          height={height}
          onChange={handleOnChange}
        />
      )}
    </div>
  )
}
