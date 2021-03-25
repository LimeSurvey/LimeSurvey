CKEditor Accessibility Checker Changelog
========================================

[CKEditor Accessibility Checker](https://ckeditor.com/ckeditor-4/accessibility-checker/)

Copyright (c) 2014-2018, CKSource - Frederico Knabben. All rights reserved.

## Version 1.1.1

New Features:

* [#240](https://github.com/cksource/ckeditor-plugin-a11ychecker/pull/240): Added Brazilian Portuguese localization. Thanks to [Guilherme Alves](https://github.com/gsag)!
* [#247](https://github.com/cksource/ckeditor-plugin-a11ychecker/issues/247): Introduced the `process` event in the `Engine` type, allowing for adding custom issue types.

Fixed Issues:

* [#233](https://github.com/cksource/ckeditor-plugin-a11ychecker/issues/233): Fixed: Balloon classes are localized, causing issues of a different testability to look the same.

## Version 1.1.0

New Features:

* [#228](https://github.com/cksource/ckeditor-plugin-a11ychecker/issues/228): Added compatibility with the new default `moono-lisa` skin.

Fixed Issues:

* [#201](https://github.com/cksource/ckeditor-plugin-a11ychecker/issues/201): `imgShouldNotHaveTitle` Quick Fix - if the image has both `title` and `alt` attributes, the `alt` will be used as the default value.

* [#185](https://github.com/cksource/ckeditor-plugin-a11ychecker/issues/185): Added a more verbose error message when jQuery is missing in the built version of .
* [#185](https://github.com/cksource/ckeditor-plugin-a11ychecker/issues/185): Added a more verbose error message when jQuery is missing in the built version of Accessibility Checker.

## Version 1.0

A brand new CKEditor plugin that lets you inspect accessibility level of content created in CKEditor and immediately solve any issues that are found. For an overview of its features, see the [Accessibility Checker website](https://ckeditor.com/ckeditor-4/accessibility-checker/).

It is built upon three key elements:

* User Interface optimized for quick problem solving.
* Flexibility allowing you to use the accessibility checking engine of your choice (default: [Quail](http://quailjs.org/)).
* Quick Fix feature letting you fix common problems fully automatically!

All of this comes bundled with a tight integration with CKEditor.

The first release includes three language versions:

* English
* German (provided by Sebastian Peilicke of [Sopra Steria GmbH](http://www.soprasteria.de/de))
* Dutch (provided by [Dutch Government](https://www.government.nl/))
