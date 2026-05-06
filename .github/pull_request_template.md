# Thank you for contributing to LimeSurvey!

To make our work easier, please make sure to follow the instructions below.

## Guidelines

- A pull request to LimeSurvey can either be a **bug fix**, a **new feature**, or an **internal development fix** (refactoring etc).
- For bug fixes and new features, you must include the number to the Mantis issue from [bugs.limesurvey.org](https://bugs.limesurvey.org). If no issue exists yet, please create it.
- Make sure to write down exactly how to reproduce a bug.
- For smaller internal changes, a Mantis issue is not necessary, but bigger refactoring tasks should always be discussed in a Mantis issue before implementation.

## Branch policy

- **Fixed issues** should always go to the **master** branch, UNLESS they fix an issue in a yet unreleased feature in the develop branch.
- **New features** should always go to the **major-develop** or ***minor-develop** branches. For more information about release schedule and code requirements, please see the following manual pages:
  - [LimeSurvey roadmap - Current release schedule](https://manual.limesurvey.org/LimeSurvey_roadmap#Current_release_schedule)
  - [How to contribute new features](https://manual.limesurvey.org/How_to_contribute_new_features)

## PR type

> Keep one of the below lines and delete the others.

Fixed issue #<Mantis issue number>: <Description>
New feature #<Mantis issue number>: <Description>
Dev: <Description for a change that's neither a feature nor a bug>
