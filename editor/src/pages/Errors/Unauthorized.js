export const Unauthorized = () => {
  return (
    <div
      style={{ height: '100vh' }}
      className="d-flex gap-4 flex-column justify-content-center align-items-center"
    >
      <h1>401</h1> {t('Access denied')}
    </div>
  )
}
