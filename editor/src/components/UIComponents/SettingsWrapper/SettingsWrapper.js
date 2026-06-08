import Accordion from 'react-bootstrap/Accordion'

export const SettingsWrapper = ({
  title,
  children,
  isDefaultOpen = false,
  isAdvanced = false,
  simpleSettings = false,
}) => {
  if ((!isAdvanced && simpleSettings) || (isAdvanced && title === ' ')) {
    return <>{children}</>
  } else if (isAdvanced && !simpleSettings) {
    return (
      <Accordion defaultActiveKey="0">
        <Accordion.Item eventKey={isDefaultOpen ? '0' : '1'}>
          <Accordion.Header>{title}</Accordion.Header>
          <Accordion.Body>{children}</Accordion.Body>
        </Accordion.Item>
      </Accordion>
    )
  }

  return <></>
}
