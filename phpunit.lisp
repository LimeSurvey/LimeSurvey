; Bootstrap for this file
(php
  (list
    ('req "application/controllers/AdminController.php")
    ('req "application/core/LSYii_Controller.php")
    ('req "application/helpers/remotecontrol/remotecontrol_handle.php")
    ('req "tests/bootstrap.php")
    ('req "vendor/autoload.php")))

; PHP Unit test
(test-class 'remotecontrol_handle
  (constructor (new 'AdminController "dummyid"))

  (test-method
    'add_participants
    (set (participant (list (map ("firstname" "John")))))
    (set (surveyId 1))
    (set (sessionKey "abc123"))
    (arguments sessionKey surveyId participant 'false)
    (result (list (map ("firstname" "John")))))

  (test-method
    'foo
    (arguments 1 2 3)
    (result 10))
)
