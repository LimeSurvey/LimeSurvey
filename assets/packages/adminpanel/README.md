# new admin panel

## whats new?

### sidebar rendering

* sidebar is now rendered via vuejs2
* based on vuex state management
* menus and entries will be collected via ajax
* questiongroups and questions are also collected by ajax
* sidebar is now resizeable by dragging
* maximum size: 50% of screen

### menus and menuentries

* new system for editing and creating menuentries in the sidebar and the quickmenu
* menus and entries are now items in the database and can be user and/or surveyspecific
* integrated menu positions are side, collapsed, top and bottom
* custom menus are possible, but not yet integrated
* nested menus are possible, but not yet integrated

### admin settings

* admin general settings removed and uncluttered
* new menuentries for the different settings
* new survey wizard -> creating a new survey is now easier
* editing surveys is mostly pjaxed -> reduced loading time

### roadmap

* question and questiongroup editing will be simplified and compressed
* switch between simple and advanced editing.
* sidemenu will be bound to current status, giving the rendering more flexibility
* topbar and bottombar will be integrated for custom menus and

## developement instructions

### nodejs

Please make sure you have nodejs and npm installed

### install requirements

Please run `npm install` while in this folder
All necessary packages will be installed.

### compiling assets

#### webpack

Webpack compiles the javascript assets and the inline styles of the vue-components.

It is run with the command `webpack` or `webpack --watch` for watching changes and automatically recompiling on change.

#### gulp

Gulp is used for compiling the SCSS and compiling the production version of the javascript

To compile the SCSS gun `gulp sass` or `gulp sass:watch` for watching changes and recompiling on change.

To create a production ready version of the javascript files run `gulp compress`.

