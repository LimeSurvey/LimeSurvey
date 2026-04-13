import { Input, Select, ToggleButtons } from 'components/UIComponents'
import { getOnOffOptions, getYesNoOptions } from 'helpers/options'

export const getOtherAttributes = () => ({
  MIN_NUMBER_FILES: {
    component: Input,
    attributePath: 'attributes.min_num_of_files',
    props: {
      labelText: t('Min number of files'),
      dataTestId: 'min-number-of-files',
      type: 'number',
      defaultValue: '1',
    },
  },
  ALLOWED_FILE_TYPES: {
    component: Input,
    attributePath: 'attributes.allowed_filetypes',
    props: {
      labelText: t('Allowed file types'),
      dataTestId: 'allowed-file-types',
      defaultValue: 'png, gif, doc, odt, jpg, jpeg',
    },
  },
  MAXIMUM_FILE_SIZE_ALLOWED: {
    component: Input,
    attributePath: 'attributes.max_filesize',
    props: {
      labelText: t('Max file size (kB)'),
      dataTestId: 'maximum-file-size-allowed',
      type: 'number',
      min: 1,
      max: 1024 * 1024 * 1024,
      defaultValue: '1024',
    },
  },
  MAX_NUMBER_FILES: {
    component: Input,
    attributePath: 'attributes.max_num_of_files',
    props: {
      labelText: t('Max number of files'),
      dataTestId: 'max-number-of-files',
      type: 'number',
      min: 1,
      defaultValue: '1',
    },
  },
  CHOICE_HEADER: {
    component: Input,
    attributePath: 'attributes.choice_title',
    props: {
      labelText: t('Choice header'),
      dataTestId: 'choice-header',
    },
  },
  RANK_HEADER: {
    component: Input,
    attributePath: 'attributes.rank_title',
    props: {
      labelText: t('Rank header'),
      dataTestId: 'rank-header',
    },
  },
  SHOW_GRAND_TOTAL: {
    component: ToggleButtons,
    attributePath: 'attributes.show_grand_total',
    props: {
      labelText: t('Show grand total'),
      dataTestId: 'show-grand-total',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  SHOW_TOTALS_FOR: {
    component: Select,
    attributePath: 'attributes.show_totals',
    props: {
      id: 'use-slider-layout',
      labelText: t('Show totals for'),
      dataTestId: 'show-totals-for',
      options: [
        { label: t('Off'), value: 'X' },
        { label: t('Rows'), value: 'R' },
        { label: t('Columns'), value: 'C' },
        { label: t('Rows & columns'), value: 'B' },
      ],
    },
  },
  INSERT_PAGE_BREAK_IN_PRINTABLE_VIEW: {
    component: ToggleButtons,
    attributePath: 'attributes.page_break',
    props: {
      id: 'insert-page-break-in-printable-view',
      labelText: t('Insert page break in printable view'),
      toggleOptions: getYesNoOptions(),
      defaultValue: '0',
    },
  },
  SPSS_EXPORT_SCALE_TYPE: {
    component: Select,
    attributePath: 'attributes.scale_export',
    props: {
      labelText: t('SPSS export scale type'),
      options: [
        {
          label: t('Default'),
          value: '0',
        },
        {
          label: t('Nominal'),
          value: '1',
        },
        {
          label: t('Ordinal'),
          value: '2',
        },
        {
          label: t('Scale'),
          value: '3',
        },
      ],
    },
  },
})
