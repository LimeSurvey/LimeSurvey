export const getCommentedCheckboxOptions = () => {
  return {
    CHECKED: {
      get label() {
        return t('Checkbox is checked')
      },
      value: 'checked',
    },
    ALWAYS: {
      get label() {
        return t('No control on checkbox')
      },
      value: 'allways',
    },
    UNCHECKED: {
      get label() {
        return t('Checkbox is unchecked')
      },
      value: 'unchecked',
    },
  }
}
