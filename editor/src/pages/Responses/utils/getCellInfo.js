export const getCellInfo = (cell) => {
  const question = cell.column.columnDef?.meta?.question

  if (!question) {
    return {}
  }

  let meta = {
    ...cell?.column?.columnDef?.meta,
  }

  return { values: [...cell.getContext().getValue()], ...meta }
}
