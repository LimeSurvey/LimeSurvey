import { cloneDeep } from 'lodash'

export const InsertElementAndIncrementProperty = (
  array = [],
  element,
  index = array.length,
  property
) => {
  const newArray = cloneDeep(array)
  newArray.splice(index, 0, element)

  if (property) {
    for (let i = index; i < newArray.length; i++) {
      newArray[i][property] = i + 1
    }
  }

  return newArray
}
