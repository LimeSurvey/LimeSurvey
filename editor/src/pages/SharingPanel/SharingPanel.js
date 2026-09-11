import classNames from 'classnames'
import { useParams } from 'react-router-dom'
import { Container } from 'react-bootstrap'

import { PAGES, STATES, SURVEY_MENU_TITLES } from 'helpers'
import { SharingOverview } from 'components/SharingPanel'
import { PluginSlot } from 'plugins/PluginSlot'
import { PLUGIN_SLOTS } from 'plugins/slots'

import { LeftSideBar } from '../../pages/Editor'
import { useAppState } from 'hooks'
import { useEffect } from 'react'

export const SharingPanel = () => {
  const { menu, surveyId } = useParams()
  const shouldHaveRightMargin = menu !== SURVEY_MENU_TITLES.sharingOverview
  const [, setTopbarConfig] = useAppState(STATES.TOPBAR_CONFIG, {})

  const renderCurrentMenu = () => {
    switch (menu) {
      case SURVEY_MENU_TITLES.sharingOverview:
        return <SharingOverview />
      default:
        return null
    }
  }

  useEffect(() => {
    setTopbarConfig({
      surveyId,
      showShareButton: false,
      showShareActionButton: true,
      showAddQuestionButton: false,
      pageName: PAGES.SHARE,
    })
  }, [surveyId])

  return (
    <>
      <Container className="p-0" fluid>
        <div
          id="content"
          data-testid="editor"
          className="d-flex position-relative"
        >
          <div
            className={classNames('main-body position-relative', {
              'right-side-margin': shouldHaveRightMargin,
            })}
          >
            <LeftSideBar
              surveyId={surveyId}
              page={PAGES.SHARE}
              showSidebarCloseButton={false}
            />
            {renderCurrentMenu()}
            <PluginSlot slotName={PLUGIN_SLOTS.SHARING_PANEL_EXTRA_MENU} />
          </div>
        </div>
      </Container>
    </>
  )
}
