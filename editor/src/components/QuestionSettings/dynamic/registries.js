import { Input, Select, ToggleButtons } from 'components/UIComponents'
import { RatingItems } from 'components/UIComponents/RatingItems'
import {
  getColumnWidthOptions,
  getCommentedCheckboxOptions,
  getDisplayButtonTypeOptions,
  getLabelWrapperWidthOptions,
  getOnOffOptions,
  getOrderOptions,
  getSliderLayoutOptions,
  getVisualizationOptions,
  getYesNoOptions,
} from 'helpers/options'

import { DefaultValuesActions } from '../DefaultValuesActions'
import { ImageAttributes } from '../attributes/ImageAttributes'
import { QuestionCodeAttribute } from '../attributes/QuestionCodeAttribute'
import { QuestionTypeAttribute } from '../attributes/QuestionTypeAttribute'
import { statisticsGraphs } from '../../../pages/Responses/components/ResponsesStatistics/ChartsUtils'

// Referenced from the react/*.xml files via <component>
export const componentRegistry = {
  Input,
  Select,
  ToggleButtons,
  ImageAttributes,
  QuestionCodeAttribute,
  QuestionTypeAttribute,
  DefaultValuesActions,
  RatingItems,
}

// Referenced via <optionsPreset> (+ optional <optionsVariant>)
export const optionsPresets = {
  yesNo: (variant) => getYesNoOptions(variant || undefined),
  onOff: (variant) => getOnOffOptions(variant || undefined),
  order: () => getOrderOptions(),
  sliderLayout: () => getSliderLayoutOptions(),
  visualization: () => getVisualizationOptions(),
  displayButtonType: () => Object.values(getDisplayButtonTypeOptions()),
  columnWidth: () => getColumnWidthOptions(),
  labelWrapperWidth: () => getLabelWrapperWidthOptions(),
  commentedCheckbox: () => {
    const options = getCommentedCheckboxOptions()
    return [options.CHECKED, options.ALWAYS, options.UNCHECKED].map(
      ({ label, value }) => ({ label, value })
    )
  },
  mandatory: () => [
    { name: t('On'), value: true },
    { name: t('Soft'), value: 'S' },
    { name: t('Off'), value: false },
  ],
  statisticsGraph: () => [
    { label: t("Don't show"), value: statisticsGraphs.DONT_SHOW },
    { label: t('Bar chart'), value: statisticsGraphs.BAR_CHART },
    { label: t('Pie chart'), value: statisticsGraphs.PIE_CHART },
    { label: t('Radar chart'), value: statisticsGraphs.RADAR },
    { label: t('Line chart'), value: statisticsGraphs.LINE },
    { label: t('Polar chart'), value: statisticsGraphs.POLAR_AREA },
  ],
}

// Used when an attribute has no <component> overlay
export const inputtypeFallback = {
  text: { component: Input },
  integer: { component: Input, props: { type: 'number' } },
  columns: { component: Input, props: { type: 'number' } },
  textarea: {
    component: Input,
    props: { as: 'textarea', type: 'textarea', role: 'textarea', rows: '4' },
  },
  singleselect: { component: Select },
  switch: { component: ToggleButtons },
  buttongroup: { component: ToggleButtons },
}
