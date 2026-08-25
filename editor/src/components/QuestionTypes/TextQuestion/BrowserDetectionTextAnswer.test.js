import React from 'react'
import { render, screen } from '@testing-library/react'

import { BrowserDetectionTextAnswer } from './BrowserDetectionTextAnswer'

// Capture the props/handlers passed to the (mocked) react-leaflet primitives so
// the tests can simulate map clicks and marker drags.
let capturedMapEvents = {}
let capturedMarkerProps = {}

jest.mock('leaflet', () => ({
  __esModule: true,
  default: {
    Icon: class {
      constructor(options) {
        this.options = options
      }
    },
  },
}))

jest.mock('react-leaflet', () => {
  const ReactMock = require('react')
  return {
    __esModule: true,
    MapContainer: ({ children, center }) => (
      <div data-testid="map" data-center={JSON.stringify(center)}>
        {children}
      </div>
    ),
    TileLayer: () => <div data-testid="tile-layer" />,
    Popup: ({ children }) => <div data-testid="popup">{children}</div>,
    Marker: ReactMock.forwardRef((props, ref) => {
      capturedMarkerProps = props
      if (ref) {
        // Fake leaflet marker instance used by the dragend handler.
        ref.current = { getLatLng: () => ({ lat: 10.5, lng: 20.25 }) }
      }
      return (
        <div
          data-testid="marker"
          data-position={JSON.stringify(props.position)}
          data-draggable={String(props.draggable)}
        >
          {props.children}
        </div>
      )
    }),
    useMapEvents: (handlers) => {
      capturedMapEvents = handlers
      return null
    },
    useMap: () => ({ setView: jest.fn() }),
  }
})

const renderMap = (props = {}) =>
  render(
    <BrowserDetectionTextAnswer
      attributes={{ location_mapservice: '100' }}
      {...props}
    />
  )

beforeEach(() => {
  global.t = (key) => key
  global.st = (key) => key
  capturedMapEvents = {}
  capturedMarkerProps = {}
})

describe('BrowserDetectionTextAnswer - editable location map', () => {
  it('renders the map with a draggable marker when location_mapservice is 100', () => {
    renderMap()

    expect(screen.getByTestId('map')).toBeInTheDocument()
    expect(screen.getByTestId('marker')).toHaveAttribute(
      'data-draggable',
      'true'
    )
  })

  it('uses the provided value as the initial marker position', () => {
    renderMap({ value: '48.1;16.3' })

    expect(screen.getByTestId('marker')).toHaveAttribute(
      'data-position',
      JSON.stringify([48.1, 16.3])
    )
  })

  it('falls back to the default center when no valid value is provided', () => {
    renderMap({ value: 'not-a-coordinate' })

    expect(screen.getByTestId('marker')).toHaveAttribute(
      'data-position',
      JSON.stringify([53.61422133647984, 9.972816890552014])
    )
  })

  it('calls onChange with the clicked coordinates', () => {
    const onLocationChange = jest.fn()
    renderMap({ onLocationChange })

    capturedMapEvents.click({ latlng: { lat: 12.34, lng: 56.78 } })

    expect(onLocationChange).toHaveBeenCalledWith('12.34;56.78')
  })

  it('calls onChange with the marker position after dragging', () => {
    const onLocationChange = jest.fn()
    renderMap({ onLocationChange })

    capturedMarkerProps.eventHandlers.dragend()

    expect(onLocationChange).toHaveBeenCalledWith('10.5;20.25')
  })

  it('does not render the map when location_mapservice is not 100', () => {
    render(
      <BrowserDetectionTextAnswer attributes={{ location_mapservice: '0' }} />
    )

    expect(screen.queryByTestId('map')).not.toBeInTheDocument()
  })
})
