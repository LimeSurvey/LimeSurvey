/**
 * @param {*} table
 * @returns Array of selected row IDs
 */
export const getSelectedRowIdsFromTable = (table) => {
  const selectedRows = table.getSelectedRowModel().rows
  return selectedRows.map((row) => row.original.id)
}
