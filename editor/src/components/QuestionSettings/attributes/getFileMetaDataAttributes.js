import { ToggleButtons } from 'components/UIComponents'
import { getOnOffOptions } from 'helpers/options'

export const getFileMetaDataAttributes = () => ({
  SHOW_TITLE: {
    component: ToggleButtons,
    attributePath: 'attributes.show_title',
    props: {
      labelText: t('Show title'),
      id: 'show-title',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  SHOW_COMMENT: {
    component: ToggleButtons,
    attributePath: 'attributes.show_comment',
    props: {
      labelText: t('Show comment'),
      id: 'show-comment',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
})
