import classNames from 'classnames'
import Button from 'react-bootstrap/Button'

import { useAppState } from 'hooks'
import { SideBarHeader } from 'components/SideBar'
import { CloseIcon } from 'components/icons'

import { Form } from 'react-bootstrap'

const questionSettingsOptions = [
  {
    label: 'General',
    value: 'general',
  },
  {
    label: 'Presentation',
    value: 'presentation',
  },
  {
    label: 'Privacy Policy',
    value: 'privacyPolicy',
  },
  {
    label: 'Participants',
    value: 'participants',
  },
  {
    label: 'Publication & access',
    value: 'publicationAccess',
  },
  {
    label: 'Advanced options',
    value: 'advancedOptions',
  },
]

export const SurveySettings = ({ surveyId }) => {
  const [clickedQuestionSettings, setClickedQuestionSettings] = useAppState(
    'clickedQuestionSettings',
    {
      label: 'General',
      value: 'general',
    }
  )
  const [, setEditorStructurePanelOpen] = useAppState(
    'editorStructurePanelOpen',
    true
  )

  return (
    <div className="d-flex" style={{ height: '100%' }}>
      <div
        className="survey-structure px-2"
        style={{ overflowY: 'auto', width: '290px' }}
      >
        <div className={classNames('survey-settings')}>
          <SideBarHeader className="right-side-bar-header primary">
            Survey Settings
            <Button
              variant="link"
              className="p-0 btn-close-lime"
              onClick={() => setEditorStructurePanelOpen(false)}
            >
              <CloseIcon className="text-black fill-current" />
            </Button>
          </SideBarHeader>
          {questionSettingsOptions.map((option, idx) => {
            return (
              <div
                key={`${idx}-${option.value}`}
                onClick={() => setClickedQuestionSettings({ ...option })}
                className={`px-4 py-3 d-flex align-items-center cursor-pointer rounded ${
                  clickedQuestionSettings.value === option.value
                    ? 'bg-primary text-white'
                    : 'text-black'
                }`}
              >
                <Form.Label
                  className={` cursor-pointer ${
                    clickedQuestionSettings.value === option.value
                      ? 'text-white'
                      : 'text-black'
                  } mb-0`}
                >
                  {option.label}
                </Form.Label>
              </div>
            )
          })}

          {/* <GeneralSettings
            language={survey.language}
            handleUpdate={update}
            survey={survey}
          /> */}
          {/* <PresentationSettings
            handleUpdate={update}
            showXQuestions={survey?.showXQuestions}
          /> */}
          {/* <PrivacyPolicySettings />
          <ParticipantsSettings />
          <PublicationAccessSettings handleUpdate={update} survey={survey} />
          <AdvancedOptionsSettings /> */}
        </div>
      </div>
    </div>
  )
}
