; PHP Unit test
(test-class remotecontrol_handle
  (constructor (new AdminController "dummyid"))
  (test-method add_participants
      (set (participant (list (map ("firstname" "John")))))
      (set (surveyId 1))
      (set (sessionKey "abc123"))
      (arguments sessionKey surveyId participant 'false)
      (result (list (map ("firstname" "John")))))
)
