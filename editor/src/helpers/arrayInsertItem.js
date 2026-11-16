import { cloneDeep } from 'lodash'

export const arrayInsertItem = (array, index, newItem) => {
  const clonedArray = cloneDeep(array)
  clonedArray.splice(index, 0, newItem)

  return clonedArray
}
