import { Input, Select, ToggleButtons } from 'components/UIComponents'
import { getCommentedCheckboxOptions, getOnOffOptions } from 'helpers/options'
const commentedCheckboxOptions = getCommentedCheckboxOptions()
export const getLogicAttributes = () => ({
  MINIMUM_ANSWERS: {
    component: Input,
    attributePath: 'attributes.min_answers',
    props: {
      labelText: t('Minimum answers'),
      dataTestId: 'minimum-answers',
    },
  },
  MAXIMUM_ANSWERS: {
    component: Input,
    attributePath: 'attributes.max_answers',
    props: {
      labelText: t('Maximum answers'),
      dataTestId: 'maximum-answers',
    },
  },
  COMMENT_ONLY_WHEN: {
    component: Select,
    attributePath: 'attributes.commented_checkbox',
    props: {
      labelText: t('Comment only when'),
      dataTestId: 'comment-only-when',
      options: [
        {
          label: commentedCheckboxOptions.CHECKED.label,
          value: commentedCheckboxOptions.CHECKED.value,
        },
        {
          label: commentedCheckboxOptions.ALWAYS.label,
          value: commentedCheckboxOptions.ALWAYS.value,
        },

        {
          label: commentedCheckboxOptions.UNCHECKED.label,
          value: commentedCheckboxOptions.UNCHECKED.value,
        },
      ],
    },
  },
  EQUATION: {
    component: Input,
    attributePath: 'attributes.equation',
    props: {
      labelText: t('Equation'),
      className: 'textarea',
      type: 'textarea',
      role: 'textarea',
      rows: 3,
    },
  },
  ARRAY_FILTER_EXCLUSION: {
    component: Input,
    attributePath: 'attributes.array_filter_exclude',
    props: {
      labelText: t('Array filter exclusion'),
      dataTestId: 'array-filter-exclusion',
    },
  },
  ARRAY_FILTER: {
    component: Input,
    attributePath: 'attributes.array_filter',
    props: {
      labelText: t('Array filter'),
      dataTestId: 'array-filter',
    },
  },
  ARRAY_FILTER_STYLE: {
    component: ToggleButtons,
    attributePath: 'attributes.array_filter_style',
    props: {
      labelText: t('Array filter style'),
      dataTestId: 'array-filter-style',
      toggleOptions: [
        { name: t('Hidden'), value: '0' },
        { name: t('Disabled'), value: '1' },
      ],
    },
  },
  NUMBERS_ONLY_FOR_OTHER: {
    component: ToggleButtons,
    attributePath: 'attributes.other_numbers_only',
    props: {
      labelText: t("Numbers only for 'Other'"),
      id: 'numbers-only-for-other',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  REMOVE_TEXT_OR_UNCHECK_CHECKBOX_AUTOMATICALLY: {
    component: ToggleButtons,
    attributePath: 'attributes.commented_checkbox_auto',
    props: {
      labelText: t('Remove text or uncheck checkbox automatically'),
      id: 'remove-text-or-uncheck-checkbox-automatically',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  // todo: check this if it should be of type number.
  ASSESSMENT_VALUE: {
    component: Input,
    attributePath: 'attributes.assessment_value',
    props: {
      labelText: t('Assessment value'),
      dataTestId: 'assessment-value',
    },
  },
  EXCLUSIVE_OPTIONS: {
    component: Input,
    attributePath: 'attributes.exclude_all_others',
    props: {
      labelText: t('Exclusive option'),
      dataTestId: 'exclusive-option',
    },
  },
  RANDOMIZATION_GROUP_NAME: {
    component: Input,
    attributePath: 'attributes.random_group',
    props: {
      labelText: t('Randomization group name'),
      dataTestId: 'randomization-group-name',
    },
  },
  QUESTION_VALIDATION_EQUATION: {
    component: Input,
    attributePath: 'attributes.em_validation_q',
    props: {
      labelText: t('Question validation equation'),
    },
  },
  COMMENT_MANDATORY: {
    component: ToggleButtons,
    attributePath: 'attributes.other_comment_mandatory',
    props: {
      labelText: t("'Other:' comment mandatory"),
      id: 'comment-mandatory',
      toggleOptions: getOnOffOptions(),
      defaultValue: '0',
    },
  },
  SUBQUESTION_VALIDATION_EQUATION: {
    component: Input,
    attributePath: 'attributes.em_validation_sq',
    props: {
      labelText: t('Subquestion validation equation'),
    },
  },
  SUBQUESTION_VALIDATION_TIP: {
    component: Input,
    attributePath: 'attributes.em_validation_sq_tip',
    props: {
      labelText: t('Question validation tip'),
    },
  },
  QUESTION_VALIDATION_TIP: {
    component: Input,
    attributePath: 'attributes.em_validation_q_tip',
    languageBased: true,
    props: {
      labelText: t('Question validation tip'),
    },
  },
  MAXIMUM_COLUMNS_FOR_ANSWERS: {
    component: Input,
    attributePath: 'attributes.max_subquestions',
    props: {
      labelText: t('Maximum columns for answers'),
      dataTestId: 'maximum-columns-for-answers',
      type: 'number',
    },
  },
})
