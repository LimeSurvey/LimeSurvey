// The wrapper function
module.exports = function(grunt) {
	
	// Project and task configuration
	grunt.initConfig({
		less: {
			development: {
				files: {
					"assets/css/yiistrap.css": "assets/less/yiistrap.less"
				}
			},
			production: {
				options: {
					compress: true,
					yuicompress: true,
					optimization: 2
				},
				files: {
					"assets/css/yiistrap.min.css": "assets/less/yiistrap.less"
				}
			}
		}
	});

	// Load plugins
	grunt.loadNpmTasks('grunt-contrib-less');

	// Define tasks
	grunt.registerTask('default', ['less']);

};