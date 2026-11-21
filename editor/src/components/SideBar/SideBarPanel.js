import { Fragment } from 'react'
import { Button } from 'react-bootstrap'
import { useNavigate, useParams } from 'react-router-dom'

import { SideBarHeader, SideBarPanelItem } from 'components/SideBar'
import { CloseIcon } from 'components/icons'
import { PluginSlot } from 'plugins/PluginSlot'
import { PLUGIN_SLOTS } from 'plugins/slots'

export const SideBarPanel = ({
  label,
  options = [],
  page = 'survey',
  showCloseButton = true,
}) => {
  const { surveyId } = useParams()
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
              <Fragment key={`${id}-${option.menu}`}>
                <SideBarPanelItem page={page} options={option} />
                <PluginSlot
                  slotName={PLUGIN_SLOTS.SIDEBAR_PANEL_ITEM_AFTER}
                  option={option}
                  page={page}
                />
              </Fragment>
            )
          })}
        </div>
      </div>
    </div>
  )
}
