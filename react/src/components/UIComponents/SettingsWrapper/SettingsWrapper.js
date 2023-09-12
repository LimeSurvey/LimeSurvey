import Accordion from 'react-bootstrap/Accordion'

export const SettingsWrapper = ({
  title,
  children,
  isDefaultOpen = false,
  isAdvanced = false,
}) => {
  return (
    <>
      {isAdvanced ? (
        <Accordion defaultActiveKey="0">
          <Accordion.Item eventKey={isDefaultOpen ? '0' : '1'}>
            <Accordion.Header>{title}</Accordion.Header>
            <Accordion.Body>{children}</Accordion.Body>
          </Accordion.Item>
        </Accordion>
      ) : (
        <div className="mt-3 px-1">{children}</div>
      )}
    </>
  )
}
