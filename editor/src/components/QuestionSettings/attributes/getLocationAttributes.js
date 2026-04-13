import { AlignButtons, Input, ToggleButtons } from 'components/UIComponents'
import { getYesNoOptions } from 'helpers/options'

export const getLocationAttributes = () => ({
  USE_MAPPING_SERVICE: {
    component: ToggleButtons,
    attributePath: 'attributes.location_mapservice',
    props: {
      id: 'use-mapping-service',
      labelText: t('Use mapping service'),
      optionTextClassName: 'text-sm',
      toggleOptions: [
        { name: t('Google Maps'), value: '1' },
        { name: t('OpenStreetMap'), value: '100' },
        { name: t('Off'), value: '0' },
      ],
      defaultValue: '100',
    },
  },
  MAP_POSITION: {
    component: AlignButtons,
    attributePath: 'attributes.location_mapposition',
    props: {
      id: 'map-position',
      labelText: t('Map position'),
      dataTestId: 'map-position',
      value: 'center',
    },
  },
  IP_AS_DEFAULT_LOCATION: {
    component: ToggleButtons,
    attributePath: 'attributes.location_nodefaultfromip',
    props: {
      id: 'ip-as-default-location',
      labelText: t('IP as default location'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '1',
    },
  },
  SAVE_COUNTRY: {
    component: ToggleButtons,
    attributePath: 'attributes.location_country',
    props: {
      labelText: t('Save country'),
      id: 'save-country',
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  SAVE_STATE: {
    component: ToggleButtons,
    attributePath: 'attributes.location_state',
    props: {
      labelText: t('Save state'),
      id: 'save-state',
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  SAVE_CITY: {
    component: ToggleButtons,
    attributePath: 'attributes.location_city',
    props: {
      id: 'save-city',
      labelText: t('Save city'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },

  SAVE_POSTAL_CODE: {
    component: ToggleButtons,
    attributePath: 'attributes.location_postal',
    props: {
      id: 'save-postal-code',
      labelText: t('Save postal code'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },

  ZOOM_LEVEL: {
    component: Input,
    attributePath: 'attributes.location_mapzoom',
    props: {
      id: 'zoom-level',
      type: 'number',
      labelText: t('Zoom level'),
    },
  },
  DEFAULT_POSITION: {
    component: Input,
    attributePath: 'attributes.location_defaultcoordinates',
    props: {
      dataTestId: 'default-position',
      labelText: t('Default position'),
    },
  },
})
