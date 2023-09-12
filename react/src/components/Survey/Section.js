export const Section = ({
  onClick = () => {},
  children,
  sectionRef,
  className = '',
}) => {
  return (
    <div
      onClick={onClick}
      className={`survey-section ${className}`}
      ref={sectionRef}
    >
      {children}
    </div>
  )
}
