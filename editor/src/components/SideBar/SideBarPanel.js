import { Button } from 'react-bootstrap'
import { useNavigate, useParams } from 'react-router-dom'

import { SideBarHeader, SideBarPanelItem } from 'components/SideBar'
import { CloseIcon } from 'components/icons'
import { PluginSlot } from 'plugins/PluginSlot'
import { PAGES } from 'helpers'

export const SideBarPanel = ({
  label,
  options = [],
  page = 'survey',
  showCloseButton = true,
}) => {
  const { surveyId, panel } = useParams()
  const navigate = useNavigate()

  const handleClose = () => {
    navigate(`/${page}/${surveyId}`)
  }

  return (
    <div data-testid="survey-menu-panel" className="d-flex h-100">
      <div className="survey-structure">
        <div className={'survey-menu'}>
          <SideBarHeader className="right-side-bar-header primary">
            {label}
            {showCloseButton && (
              <Button
                variant="link"
                className="p-0 btn-close-lime"
                onClick={handleClose}
                data-testid="btn-close-survey-menu-panel"
              >
                <CloseIcon className="text-black fill-current" />
              </Button>
            )}
          </SideBarHeader>
          {options.map((option, id) => {
            return (
              <SideBarPanelItem
                key={`${id}-${option.menu}`}
                page={page}
                options={option}
              />
            )
          })}
          {page == PAGES.SHARE && panel == 'sharingSettings' && (
            <PluginSlot slotName="sidebarpanel:sharing:sharingSettings:bottom" />
          )}
        </div>
      </div>
    </div>
  )
}
