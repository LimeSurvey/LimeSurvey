Tests are divided into two folders:

    unit/
    functional/

Unit tests are tests that NOT use the Facebook webdriver or headless browser. Functional tests are tests that do.

Functional tests are divided into back-end (survey administration) and front-end (survey taking) tests.

Unit tests are divided into models, helpers, controllers, etc...

## Future directions

Something to consider for the future is the time-constraint of the CI. Integrity tests take longer to execute,
especially when scripting the browser. They are also hard to setup and run locally.

Future folder structure:

* unit/ - for unit-tests, using mocks, not depending on database except for AR cache setup
* functional/ - tests interacting with database and filesystem
* integrity/ - tests scripting the browser

They should be run in that order, since unit tests are faster than functional tests, which are faster than
integrity tests.

Unit tests should be able to run in parallel.
