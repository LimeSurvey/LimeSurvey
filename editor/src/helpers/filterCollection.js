export const filterCollection = (collection, callback) => {
  if (Array.isArray(collection)) {
    // If the collection is an array, filter it directly
    return collection.filter(callback)
  } else if (typeof collection === 'object' && collection !== null) {
    // If the collection is an object, filter it by converting to an array of entries
    return Object.fromEntries(
      Object.entries(collection).filter(([key, value]) =>
        callback(value, key, collection)
      )
    )
  } else {
    throw new TypeError('Input should be an array or an object')
  }
}
