export const SortByProperty = (array, property) => {
  const newArray = [...array]

  newArray.sort((a, b) => {
    const propA = a[property]
    const propB = b[property]

    if (propA < propB) {
      return -1
    }
    if (propA > propB) {
      return 1
    }
    return 0
  })

  return newArray
}
