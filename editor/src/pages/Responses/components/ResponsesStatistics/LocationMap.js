import {
  useEffect,
  useId,
  useMemo,
  useRef,
  useState,
  useSyncExternalStore,
} from 'react'
import { format } from 'util'
import { Button, Form } from 'react-bootstrap'
import {
  MapContainer,
  Marker,
  Popup,
  TileLayer,
  useMap,
  useMapEvents,
} from 'react-leaflet'
import L from 'leaflet'

import { useQuestionLocations } from 'hooks'
import { useIsInViewport } from 'hooks/useInViewport'

// Hamburg; only shown until the first fetch reports where the responses are.
const DEFAULT_CENTER = [53.55, 9.99]
const DEFAULT_ZOOM = 5
const FIT_MAX_ZOOM = 12
const POINT_LIMIT = 500
// Web Mercator's latitude limit, so fitting doesn't zoom out past the tiles.
const WORLD_BBOX = [
  [-85, -180],
  [85, 180],
]
// Quiet time after a pan/zoom before the viewport is fetched, so a run of
// zoom steps or drags costs one request.
const VIEWPORT_DEBOUNCE_MS = 400

const PIN_ICON = L.divIcon({
  className: 'responses-statistics-map-pin',
  html: '<span class="responses-statistics-map-pin-dot"></span>',
  iconSize: [22, 22],
  iconAnchor: [11, 11],
  popupAnchor: [0, -12],
})

// GDPR: the tiles come from a third party that sees the viewer's IP, so
// nothing (tiles or location API) loads until the user opts in.
const CONSENT_STORAGE_KEY = 'responses-statistics-map-consent'
const consentListeners = new Set()
let hasSessionConsent = false

const readStoredConsent = () => {
  try {
    return localStorage.getItem(CONSENT_STORAGE_KEY) === '1'
  } catch {
    return false
  }
}

const grantConsent = (remember) => {
  hasSessionConsent = true
  if (remember) {
    try {
      localStorage.setItem(CONSENT_STORAGE_KEY, '1')
    } catch {
      // ignore quota / disabled storage
    }
  }
  consentListeners.forEach((listener) => listener())
}

const subscribeConsent = (listener) => {
  consentListeners.add(listener)
  return () => consentListeners.delete(listener)
}

const getConsent = () => hasSessionConsent || readStoredConsent()

const useMapConsent = () => useSyncExternalStore(subscribeConsent, getConsent)

const MapConsent = () => {
  const rememberId = useId()
  const [remember, setRemember] = useState(false)
  return (
    <div className="responses-statistics-map-consent">
      <i className="ri-map-pin-line responses-statistics-map-consent-icon"></i>
      <strong>{t('Load map')}</strong>
      <p className="mb-0">
        {t(
          'This map is provided by a third-party service. By loading the map, you agree that data may be transmitted to the map provider.'
        )}
      </p>
      <Form.Check
        type="checkbox"
        id={rememberId}
        label={t('Always load maps')}
        checked={remember}
        onChange={(event) => setRemember(event.target.checked)}
      />
      <Button
        variant="primary"
        size="sm"
        onClick={() => grantConsent(remember)}
      >
        {t('Load map')}
      </Button>
    </div>
  )
}

// Rounded so a sub-metre nudge doesn't produce a new query key.
const roundCoordinate = (value) => Math.round(value * 10000) / 10000

const boundsOf = (map) => {
  const bounds = map.getBounds()
  return {
    south: roundCoordinate(bounds.getSouth()),
    west: roundCoordinate(bounds.getWest()),
    north: roundCoordinate(bounds.getNorth()),
    east: roundCoordinate(bounds.getEast()),
  }
}

// Reports the viewport once the user pauses after panning/zooming so the
// points get refetched; a gesture that starts meanwhile cancels the pending
// report.
const ViewportTracker = ({ onChange }) => {
  const timer = useRef(null)
  const cancel = () => clearTimeout(timer.current)
  const map = useMapEvents({
    movestart: cancel,
    zoomstart: cancel,
    moveend: () => {
      cancel()
      timer.current = setTimeout(
        () => onChange(boundsOf(map)),
        VIEWPORT_DEBOUNCE_MS
      )
    },
  })
  useEffect(() => cancel, [])
  return null
}

// Extent of the points, as a Leaflet bounds literal.
const bboxOf = (points) =>
  points.length
    ? [
        [
          Math.min(...points.map((point) => point.lat)),
          Math.min(...points.map((point) => point.lng)),
        ],
        [
          Math.max(...points.map((point) => point.lat)),
          Math.max(...points.map((point) => point.lng)),
        ],
      ]
    : null

// Zooms to the responses once, when the whole-world fetch first arrives.
// Later moves belong to the user.
const InitialFit = ({ bbox, onDone }) => {
  const map = useMap()
  useEffect(() => {
    if (!bbox) {
      return
    }
    // No animation, so the bounds reported below are the fitted ones.
    map.fitBounds(bbox, {
      padding: [32, 32],
      maxZoom: FIT_MAX_ZOOM,
      animate: false,
    })
    onDone(boundsOf(map))
  }, [map, bbox, onDone])
  return null
}

/**
 * Response locations of a map question (short text with a mapping service)
 * as pins; the visible area is refetched whenever the map is moved or zoomed.
 */
export const LocationMap = (props) =>
  useMapConsent() ? <ConsentedLocationMap {...props} /> : <MapConsent />

const ConsentedLocationMap = ({ surveyId, questionCode, fields, filters }) => {
  const [containerRef, isInView] = useIsInViewport(null, {
    initialInView: false,
  })
  const [shouldLoad, setShouldLoad] = useState(false)
  useEffect(() => {
    if (isInView) {
      setShouldLoad(true)
    }
  }, [isInView])

  // null = whole world, used until the map has been fitted to the responses.
  const [bounds, setBounds] = useState(null)
  const [isFitted, setIsFitted] = useState(false)

  const { points, total, isLoading, isFetching, isPlaceholderData } =
    useQuestionLocations(surveyId, questionCode, {
      enabled: shouldLoad,
      fields,
      bounds,
      filters,
      limit: POINT_LIMIT,
    })

  // The whole-world result (capped at POINT_LIMIT) decides the initial view.
  // A capped sample may miss responses, so fall back to the whole world.
  const bbox = useMemo(() => {
    if (bounds !== null || isPlaceholderData) {
      return null
    }
    return total > points.length ? WORLD_BBOX : bboxOf(points)
  }, [bounds, isPlaceholderData, points, total])

  // New filters can move the responses elsewhere; fit again on the next
  // whole-world result. Keyed on the serialized filters since the object
  // identity changes on every parent render.
  const filtersKey = JSON.stringify(filters ?? {})
  useEffect(() => {
    setBounds(null)
    setIsFitted(false)
  }, [filtersKey])

  const fitDone = (fittedBounds) => {
    setIsFitted(true)
    setBounds(fittedBounds)
  }

  const markers = useMemo(
    () =>
      points.map((point) => (
        <Marker
          key={point.id}
          position={[point.lat, point.lng]}
          icon={PIN_ICON}
        >
          <Popup>
            <strong>{format(t('Response ID: %s'), point.id)}</strong>
            <br />
            {point.lat}, {point.lng}
          </Popup>
        </Marker>
      )),
    [points]
  )

  const isTruncated = total > points.length
  const hasNoLocations = shouldLoad && !isLoading && total === 0 && !bounds

  return (
    <div ref={containerRef} className="responses-statistics-map">
      {shouldLoad && (
        <MapContainer
          center={DEFAULT_CENTER}
          zoom={DEFAULT_ZOOM}
          scrollWheelZoom={false}
          className="responses-statistics-map-canvas"
        >
          <TileLayer
            url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
            attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
          />
          {!isFitted && bbox && <InitialFit bbox={bbox} onDone={fitDone} />}
          {isFitted && <ViewportTracker onChange={setBounds} />}
          {markers}
        </MapContainer>
      )}
      {(!shouldLoad || isLoading) && (
        <div className="responses-statistics-map-status">
          <span className="loader"></span>
        </div>
      )}
      {shouldLoad && !isLoading && isFetching && (
        <div className="responses-statistics-map-refreshing">
          <span className="loader"></span>
        </div>
      )}
      {hasNoLocations && (
        <div className="responses-statistics-map-status">
          {t('No responses with a location yet.')}
        </div>
      )}
      <div className="responses-statistics-map-footer">
        {isTruncated
          ? format(
              t(
                'Showing %s of %s locations in this area. Zoom in to see them all.'
              ),
              points.length,
              total
            )
          : total === 1
            ? t('1 location in this area')
            : format(t('%s locations in this area'), total)}
      </div>
    </div>
  )
}
