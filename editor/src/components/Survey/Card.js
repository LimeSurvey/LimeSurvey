import classNames from 'classnames'

export const Card = ({ children, className, style }) => {
  return (
    <div className={classNames(className, 'survey-card')} style={style}>
      {children}
    </div>
  )
}
