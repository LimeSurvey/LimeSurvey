Package.describe({
  name: 'ilikenwf:nested-sortable',
  version: '0.0.1',
  // Brief, one-line summary of the package.
  summary: 'A jQuery plugin that extends Sortable UI functionalities to nested lists.',
  // URL to the Git repository containing the source code for this package.
  git: 'https://github.com/ilikenwf/nestedSortable',
  // By default, Meteor will default to using README.md for documentation.
  // To avoid submitting documentation, set this field to null.
  documentation: 'README.md'
});

Package.onUse(function(api) {
  api.versionsFrom('1.1.0.2');
  
  api.use('jquery', 'client');
  api.use('mizzao:jquery-ui', 'client');
  
  api.imply('jquery', 'client');
  
  api.addFiles('jquery.mjs.nestedSortable.js', 'client');
});

Package.onTest(function(api) {
  api.use('tinytest');
  api.use('ilikenwf:nested-sortable');
  api.addFiles('meteor/nested-sortable-tests.js');
});
