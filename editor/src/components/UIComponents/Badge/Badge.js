import classNames from 'classnames'

export const Badge = ({ children, className }) => {
  return (
    <div className={classNames('badge-component', className)}>{children}</div>
  )
}
