export const getConditionTypeInfo = () => {
  return {
    SOURCE: {
      QUESTION: 'Question',
      PARTICIPANT_DATA: 'Token',
    },
    TARGET: {
      ANSWER_OPTIONS: 'Answer',
      CONSTANT: 'Constant',
      ANSWER_OF_OTHER_QUESTION: 'Question',
      REGEX: 'RegExp',
      PARTICIPANT_DATA: 'Token',
    },
  }
}
