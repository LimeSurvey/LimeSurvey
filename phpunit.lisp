; Bootstrap for this file
(php (list
    ('req "vendor/autoload.php")
    ('req "tests/bootstrap.php")
    ('req "application/helpers/remotecontrol/remotecontrol_handle.php")
    ('req "application/controllers/AdminController.php")
 ))

; PHP Unit test
(test-class 'remotecontrol_handle
  (constructor (new 'AdminController "dummyid"))
  (test-method 'add_participants
      (set (participant (list (map ("firstname" "John")))))
      (set (surveyId 1))
      (set (sessionKey "abc123"))
      (arguments sessionKey surveyId participant 'false)
      (result (list (map ("firstname" "John")))))
)
