# How to contribute

Third-party patches are essential for keeping LimeSurvey great. We simply can't
access the huge number of platforms and myriad configurations for running
LimeSurvey. We want to keep it as easy as possible to contribute changes that
get things working in your environment. There are a few guidelines that we
need contributors to follow so that we can have a chance of keeping on
top of things.

## Getting started

* Make sure you have a [LimeSurvey account](https://www.limesurvey.org)
* Make sure you have a [GitHub account](https://github.com/signup/free)
* Submit a ticket at https://bugs.limesurvey.org for your issue, assuming one does not already exist.
  * Clearly describe the issue including steps to reproduce when it is a bug.
  * Make sure you fill in the earliest version that you know has the issue.
* Fork the repository on GitHub

## Making changes

* Create a topic branch from where you want to base your work.
  * This is usually the master branch.
  * Only target release branches if you are certain your fix must be on that
    branch.
  * To quickly create a topic branch based on master; `git checkout -b
    fix/master/my_contribution master`. Please avoid working directly on the
    `master` branch.
* Make commits of logical units.
* Check for unnecessary whitespace with `git diff --check` before committing.
* Make sure your commit messages are in the proper format - check out our 
  [commit message guidelines](https://manual.limesurvey.org/Standard_for_Git_commit_messages).


## Writing translatable code

We use gettext-tooling to extract user-facing strings and pull in translations 
based on the user's locale at runtime. In order for this tooling to work, all 
user-facing strings must be wrapped in the `gT()` translation function, so they 
can be extracted into files for the translators.

When adding user-facing strings to your work, follow these guidelines:
* Use full sentences. Strings built up out of concatenated bits are hard to translate.
* Use string formatting instead of interpolation.
    Ex. `sprintf(gT('Creating new user %s.'), $sUsername)`
* Use `ngT()` for pluralization.

It is the responsibility of contributors to ensure that all
user-facing strings are marked in new PRs before merging.


## Submitting changes

* Push your changes to a topic branch in your fork of the repository.
* Submit a pull request to the repository in the LimeSurvey organization.
* Update your bug tracker ticket to mark that you have submitted code and are ready for it to be reviewed.
  * Include a link to the pull request in the ticket.
* The core team looks at Pull Requests on a regular basis in a weekly triage
* After feedback has been given we expect responses within two weeks. After two
  weeks we may close the pull request if it isn't showing any activity.

# Additional resources

* [Bug tracker (Mantis)](https://bugs.limesurvey.org)
* [Standard for Git commit messages](https://manual.limesurvey.org/Standard_for_Git_commit_messages)
* [General GitHub documentation](https://help.github.com/)
* [GitHub pull request documentation](https://help.github.com/articles/creating-a-pull-request/)
* #limesurvey IRC channel on freenode.org ([Archive](https://www.limesurvey.org/community/irc-logs-limesurvey)
