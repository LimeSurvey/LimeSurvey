import Accordion from 'react-bootstrap/Accordion'

export const SettingsWrapper = ({
  title,
  children,
  isDefaultOpen = false,
  isAdvanced = false,
  simpleSettings = false,
  isExpanded,
  onToggle,
}) => {
  if ((!isAdvanced && simpleSettings) || (isAdvanced && title === ' ')) {
    return <>{children}</>
  } else if (isAdvanced && !simpleSettings) {
    const isControlled = typeof isExpanded === 'boolean'

    if (isControlled) {
      return (
        <Accordion
          activeKey={isExpanded ? '0' : null}
          onSelect={(eventKey) => onToggle?.(eventKey === '0')}
        >
          <Accordion.Item eventKey="0">
            <Accordion.Header>{title}</Accordion.Header>
            <Accordion.Body>{children}</Accordion.Body>
          </Accordion.Item>
        </Accordion>
      )
    }

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
