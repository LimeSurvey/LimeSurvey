import { Input } from 'components/UIComponents'

export const groupAttributes = [
  {
    component: Input,
    attributePath: 'l10ns.[language].groupName',
    props: {
      labelText: 'Title',
      dataTestId: 'group-title',
      noPermissionDisabled: true,
    },
  },
  {
    component: Input,
    attributePath: 'l10ns.[language].description',
    props: {
      labelText: 'Description',
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
      labelText: 'Randomization group',
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
      labelText: 'Condition',
      dataTestId: 'condition-group',
      noPermissionDisabled: true,
      type: 'textarea',
      role: 'textarea',
    },
  },
]
