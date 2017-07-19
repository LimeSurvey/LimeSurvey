# Admin panel

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

Compilation of assets is completely controllable by gulp.

Here is a list of possible gulp commands:

|Command                   | Description                                                |
|--------------------------|------------------------------------------------------------|
|`gulp`                    | Just compile all assets into the build folder              |
|`gulp compile`            | Long version for `gulp`                                    |
|`gulp compile:production` | Compile all assets into production ready minified versions |
|`gulp watch`              | Compile all assets and then watch for changes              |


Here are subcommands that are not neccessary normally


|Command                   | Description                                                |
|--------------------------|------------------------------------------------------------|
|`gulp sass`               | Only compile sass                                          |
|`gulp sass:production`    | Only compile sass for production                           |
|`gulp webpack:production` | Only compile js through webpack                            |
|`gulp webpack`            | Only compile js through webpack for production             |
|`gulp sass:watch`         | Watch sass assets for change and compile on change         |
|`gulp webpack:watch`      | Watch js assets for change and compile on change           |
|`gulp compress`           | Compress assets to a minified version                      |
