# A11yFirst Help

## Summary

This plugin adds the A11yFirst Help menu button to the editor toolbar, which
launches a dialog box that provides explanatory text on topics that help
authors understand the accessibility issues of editing web documents.

It compliments and provides specific information on the use of the full suite
of A11yFirst plugins: Heading / Paragraph, Character Style, A11yFirst Image
and A11yFirst Link.

## Overview

Each of the A11yFirst plugins embodies a new approach to authoring accessible
web documents. Rather than relying upon a reactive model that detects problems
after they exist, the A11yFirst approach is proactive. It enables authors to
create content that is accessible during the authoring process, by providing
feedback and validation on a feature-by-feature basis.

For example, once an author becomes familiar with the requirements for, say,
adding images to documents, it becomes second nature to provide alternative
text when the image is added, and to consider whether the image needs a longer
description in the document.

## Features

The A11yFirst Help dialog box contains the following information:

* How to get started using A11yFirst for CKEditor and why accessibility
  is important

* How to work with the A11yFirst features, as well as a few of the standard
  features included in CKEditor that are important for accessibility

* The purpose of, and contributors to, the A11yFirst Project

In particular, it includes the following topics and subtopics:

### Heading / Paragraph

* Menu items
* About headings
* About paragraph formats
* Why headings and paragraph formats are important
* More information

### List

* Working with lists
* List properties via context menu
* About lists
* Why lists are important

### Image

* Accessible image descriptions
* Alternative text
* Long descriptions for complex images
* Adding a long description in the document
* Insert a caption text box below the image
* Writing effective alternative text
* Why image descriptions are important
* More information

### Character Style

* Working with character styles
* About character styles
* Character styles vs. headings, lists and paragraph formats

### Link

* Display Text
* About links
* Why links are important
* More information

### Getting Started

* What is A11yFirst?
* Commitment to accessibility
* Document accessibility
* A different way of thinking

### About A11yFirst

* Project Description & Goals
* Project Information
* Contributors
* Sponsors
* Acknowledgements

## Installation

Please refer to the following pages in the
[A11yFirst Documentation wiki](https://github.com/a11yfirst/documentation/wiki):

* [Installing A11yFirst Plugins](https://github.com/a11yfirst/documentation/wiki/Installing-A11yFirst-Plugins)
* [Using CKEditor Builder](https://github.com/a11yfirst/documentation/wiki/Using-CKEditor-Builder)

## Configuration and Usage

Please refer to the following pages in the
[A11yFirst Documentation wiki](https://github.com/a11yfirst/documentation/wiki):

* [Configuring CKEditor](https://github.com/a11yfirst/documentation/wiki/Configuring-CKEditor)
* [Features Overview](https://github.com/a11yfirst/documentation/wiki/Features-Overview)

<!--
## Developer Notes

### Adding or Removing Help Topics

To modify the help topics contained in this plug-in, the following steps are
required:

* In `plugin.js`, update the `config.a11yFirstHelpTopics` object. The
  sequential order of the properties in this object determines the order of
  the A11yFirst Help menu items and the help dialog menu buttons.

* In `dialogs/a11yfirst-help.js`, update the `contents.children.html` string
  to include the necessary `div` elements for the help topics.

* In the `content/en` subdirectory, add the Markdown files with content for
  the help topics.

* In `content/en/setLang.js`, update the properties of the object passed to
  the `setLang` function to include all of the help topics.

* Update `content/en/build.sh` to include the processing of the Markdown files
  that contain the content for all of the help topics.

### Using the `showdown` converter

A `showdown` extension is defined in `dialogs/a11yfirst-help.js` that allows
URLs such as image `src` values to be independent of the editor installation
path. The extension object is named `basePathExt`, and it is registered with
`showdown` using the name `basePath`.
-->
