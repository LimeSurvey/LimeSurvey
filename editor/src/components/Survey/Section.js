export const Section = ({
  onClick = () => {},
  children,
  sectionRef,
  className = '',
  testId = '',
}) => {
  return (
    <div
      onClick={onClick}
      className={`survey-section ${className}`}
      ref={sectionRef}
      data-testid={testId}
    >
      {children}
    </div>
  )
}
