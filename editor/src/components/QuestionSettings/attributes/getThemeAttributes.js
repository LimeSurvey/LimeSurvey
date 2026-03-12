import { Select, ToggleButtons } from 'components/UIComponents'
import { getYesNoOptions, YESNO_STRING_BOOLEAN } from 'helpers/options'

export const getThemeAttributes = () => ({
  BUTTON_SIZE: {
    component: Select,
    attributePath: 'attributes.button_size',
    props: {
      labelText: t('Button size'),
      id: 'button-size',
      options: [
        { label: t('Default'), value: 'default' },
        { label: t('Large'), value: 'lg' },
        { label: t('Small'), value: 'sm' },
        { label: t('Extra small'), value: 'xs' },
      ],
    },
  },
  MAXIMUM_NUMBER_OF_BUTTONS_IN_A_ROW: {
    component: Select,
    attributePath: 'attributes.max_buttons_row',
    props: {
      id: 'maximum-number-of-buttons-in-a-row',
      labelText: t('Maximum number of buttons in a row'),
      options: [
        { label: t('Default'), value: 'default' },
        { label: '1', value: 'col-md-12' },
        { label: '2', value: 'col-md-6' },
        { label: '3', value: 'col-md-4' },
        { label: '4', value: 'col-md-3' },
        { label: '6', value: 'col-md-2' },
        { label: '12', value: 'col-md-1' },
      ],
    },
  },
  ALLOW_SEARCHING_DROPDOWN: {
    component: ToggleButtons,
    attributePath: 'attributes.show_search',
    props: {
      labelText: t('Allow searching of dropdown'),
      id: 'search-of-dropdown',
      toggleOptions: getYesNoOptions(YESNO_STRING_BOOLEAN),
      defaultValue: 'false',
    },
  },
  DISPLAY_TICK_SELECTED_ITEM: {
    component: ToggleButtons,
    attributePath: 'attributes.show_tick',
    props: {
      labelText: t('Display tick on selected item'),
      id: 'display-tick',
      toggleOptions: getYesNoOptions(YESNO_STRING_BOOLEAN),
      defaultValue: 'false',
    },
  },
  WIDTH_DROPDOWN_SELECTED_ENTRY: {
    component: Select,
    attributePath: 'attributes.width_entry',
    props: {
      labelText: t('Width of dropdown'),
      id: 'width-dropdown-selected-entry',
      options: [
        { label: t('Current selected entry'), value: 'true' },
        { label: t('Longest entry'), value: 'false' },
      ],
      value: 'false',
    },
  },
})
