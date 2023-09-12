import { useQuery } from '@tanstack/react-query'

import { queryClient } from 'query'

// This hook uses react-query to cache current focused question.
// It shouldn't be used for any other use cases.
export const useFocused = (focused, groupIndex, questionIndex) => {
  const { data } = useQuery({
    queryKey: ['focusedQuestion'],
    queryFn: () => {
      return {
        focused,
        groupIndex,
        questionIndex,
      }
    },
  })

  const setFocused = (updatedQuestion, groupIndex, questionIndex) => {
    unFocus()
    return queryClient.setQueryData(['focusedQuestion'], {
      focused: { ...updatedQuestion },
      groupIndex,
      questionIndex,
    })
  }

  const unFocus = () => {
    return queryClient.setQueryData(['focusedQuestion'], {})
  }

  return {
    setFocused,
    unFocus,
    ...data,
  }
}
