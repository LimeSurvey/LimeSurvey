import { Input } from 'components/UIComponents'

export const getQuestionGroupAttributes = () => [
  {
    component: Input,
    attributePath: 'l10ns.[language].groupName',
    props: {
      labelText: t('Title'),
      dataTestId: 'group-title',
      noPermissionDisabled: true,
    },
  },
  {
    component: Input,
    attributePath: 'l10ns.[language].description',
    props: {
      labelText: t('Description'),
      dataTestId: 'group-description',
      noPermissionDisabled: true,
      type: 'textarea',
      role: 'textarea',
    },
  },
  {
    component: Input,
    attributePath: 'randomizationGroup',
    props: {
      labelText: t('Randomization group name'),
      dataTestId: 'group-randomization-group',
      noPermissionDisabled: true,
      type: 'textarea',
      role: 'textarea',
    },
  },
  {
    component: Input,
    attributePath: 'gRelevance',
    props: {
      labelText: t('Condition'),
      dataTestId: 'condition-group',
      noPermissionDisabled: true,
      type: 'textarea',
      role: 'textarea',
    },
  },
]
