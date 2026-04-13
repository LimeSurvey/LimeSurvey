import classNames from 'classnames'
import { useParams } from 'react-router-dom'
import { Container } from 'react-bootstrap'

import { PAGES, SURVEY_MENU_TITLES } from 'helpers'
import { TopBar } from 'components'
import { SharingOverview } from 'components/SharingPanel'
import { PluginSlot } from 'plugins/PluginSlot'
import { PLUGIN_SLOTS } from 'plugins/slots'

import { LeftSideBar } from '../../pages/Editor'

export const SharingPanel = () => {
  const { menu, surveyId } = useParams()
  const shouldHaveRightMargin = menu !== SURVEY_MENU_TITLES.sharingOverview

  const renderCurrentMenu = () => {
    switch (menu) {
      case SURVEY_MENU_TITLES.sharingOverview:
        return <SharingOverview />
      default:
        return null
    }
  }

  return (
    <>
      <TopBar
        surveyId={surveyId}
        showShareButton={false}
        showShareActionButton={true}
        showAddQuestionButton={false}
      />
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
