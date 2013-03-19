/*global module:false*/
module.exports = function(grunt) {
	// Load grunt helpers
	grunt.loadNpmTasks('grunt-contrib');
	grunt.loadNpmTasks('grunt-text-replace');

	// Project configuration.
	grunt.initConfig({
		// Meta properties
		pkg: '<json:package.json>',
		meta: {
			banners: {
				full: '/*!\n * <%= pkg.title || pkg.name %> - @vVERSION\n' +
					' * <%=pkg.homepage%>\n' +
					' *\n' + 
					' * Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author.name %>\n' +
					' * Released under the <%= _.pluck(pkg.licenses, "type").join(", ") %> licenses\n' + 
					' * http://jquery.org/license\n' + 
					' *\n' + 
					' * Date: <%= grunt.template.today("ddd mmm d yyyy hh:MM Zo", true) %>\n' + 
					'@BUILDPROPS */',

				min:'/*! <%= pkg.name %> @vVERSION @MINBUILDPROPS| <%= pkg.homepage.replace("http://","") %> | '+
					'Licensed <%= _.pluck(pkg.licenses, "type").join(", ") %> | <%=grunt.template.today() %> */'
			}
		},

		// Directories
		dirs: { src: 'src', dist: 'dist' },

		// Styles and plugins map
		styles: {
			basic: '<%=dirs.src%>/basic.css',
			css3: '<%=dirs.src%>/css3.css'
		},
		plugins: {
			svg: { js: '<%=dirs.src%>/svg/svg.js' },
			ajax: { js: '<%=dirs.src%>/ajax/ajax.js' },
			tips: { js: '<%=dirs.src%>/tips/tips.js', css: '<%=dirs.src%>/tips/tips.css' },
			modal: { js: '<%=dirs.src%>/modal/modal.js', css: '<%=dirs.src%>/modal/modal.css' },
			viewport: { js: '<%=dirs.src%>/viewport/viewport.js' },
			imagemap: { js: '<%=dirs.src%>/imagemap/imagemap.js' },
			ie6: { js: '<%=dirs.src%>/ie6/ie6.js', css: '<%=dirs.src%>/ie6/ie6.css' }
		},

		// Actual tasks
		clean: {
			dist: 'dist/**/*' // Changed by the 'dist' command-line option (see "init" task)
		},
		concat: {
			basic: {
				src: [
					'<banner:meta.banners.full>', '<%=dirs.src%>/intro.js',
					'<%=dirs.src%>/core.js', '<%=dirs.src%>/outro.js'
				],
				dest: '<%=dirs.dist%>/basic/jquery.qtip.js'
			},
			basic_css: {
				src: [ '<banner:meta.banners.full>', '<%=dirs.src%>/core.css', '<%=styles.basic%>' ],
				dest: '<%=dirs.dist%>/basic/jquery.qtip.css'
			},
			dist: {
				// See "init" task for src
				dest: '<%=dirs.dist%>/jquery.qtip.js'
			},
			dist_css: {
				// See "init" task for src
				dest: '<%=dirs.dist%>/jquery.qtip.css'
			}
		},
		min: {
			basic: {
				src: ['<banner:meta.banners.min>', '<file_strip_banner:<%=dirs.dist%>/basic/jquery.qtip.js:block>'],
				dest: '<%=dirs.dist%>/basic/jquery.qtip.min.js'
			},
			dist: {
				src: ['<banner:meta.banners.min>', '<file_strip_banner:<%=dirs.dist%>/jquery.qtip.js:block>'],
				dest: '<%=dirs.dist%>/jquery.qtip.min.js'
			}
		},
		mincss: {
			basic: {
				files: {
					'<%=dirs.dist%>/basic/jquery.qtip.min.css': [
						'<banner:meta.banners.min>', '<file_strip_banner:<%=dirs.dist%>/basic/jquery.qtip.css:block>'
					]
				}
			},
			dist: {
				files: {
					'<%=dirs.dist%>/jquery.qtip.min.css': [
						'<banner:meta.banners.min>', '<file_strip_banner:<%=dirs.dist%>/jquery.qtip.css:block>'
					]
				}
			}
		},
		replace: {
			dist: {
				src: '<%=dirs.dist%>/**/*',
				overwrite: true
			}
		},
		lint: {
			beforeconcat: ['grunt.js', '<%=dirs.src%>/core.js', '<%=dirs.src%>/*/*.js']
		},
		watch: {
			files: '<config:lint.beforeconcat.files>',
			tasks: 'lint'
		},
		jshint: {
			options: {
				curly: true,
				eqeqeq: true,
				immed: true,
				latedef: true,
				newcap: true,
				noarg: true,
				sub: true,
				boss: true,
				eqnull: true,
				browser: true,
				undef: false
			},
			globals: {
				jQuery: true,
				'$': true
			}
		},
		uglify: {}
	});

	// Parse command line options
	grunt.registerTask('init', 'Default build', function() {
		var done = this.async();

		if(grunt.config('concat.dist.src')) { return; } // Only do it once

		// Grab command-line options, using valid defaults if not given
		var stable = grunt.option('stable') === true,
			plugins = (grunt.option('plugins') || Object.keys( grunt.config('plugins')).join(' ')).replace(/ /g, ' ').split(' '),
			styles = (grunt.option('styles') || Object.keys( grunt.config('styles')).join(' ')).replace(/ /g, ' ').split(' '),
			valid;

		// Setup JS/CSS arrays
		var js = ['<banner:meta.banners.full>', '<%=dirs.src%>/intro.js', '<%=dirs.src%>/core.js'],
			css = ['<banner:meta.banners.full>', '<%=dirs.src%>/core.css'],
			dist = grunt.option('dist');

		// Parse 'styles' option (decides which stylesheets are included)
		if(grunt.option('styles') !== 0) {
			styles.forEach(function(style, i) {
				if( (valid = grunt.config('styles.'+style)) ) {
					css.push(valid);
				}
				else { styles[i] = style+('*'.red); }
			});
		}
		else { styles = ['None']; }

		// Parse 'plugins' option (decides which plugins are included)
		if(grunt.option('plugins') !== 0) {
			plugins.forEach(function(plugin, i) {
				if( (valid = grunt.config('plugins.'+plugin)) ) {
					if(valid.js) { js.push(valid.js); }
					if(valid.css) { css.push(valid.css); }
				}
				else { plugins[i] = plugin+('*'.red); }
			});
		}
		else { plugins = ['None']; }

		// Update config
		grunt.config('concat.dist.src', js.concat(['<%=dirs.src%>/outro.js']));
		grunt.config('concat.dist_css.src', css);

		// Parse 'dist' option (decides which directory to build into)
		if(dist) {
			grunt.config('dirs.dist', dist);
			grunt.config('clean.dist', dist + '/**/*');
		}

		// Setup in-file text replacements (version, date etc)
		grunt.utils.spawn({ cmd: 'git', args: ['describe'] }, function(err, sha1) {
			var version = stable ? grunt.config('pkg.version') : sha1.substr(0,10);

			grunt.config('replace.dist.replacements', [{
				from: '@VERSION',
				to: stable ? version : version.substr(1)
			}, {
				from: '@vVERSION',
				to: stable ? 'v'+version : version
			}, {
				from: '@DATE',
				to: grunt.template.today("dd-mm-yyyy")
			}, {
				from: '@BUILDPROPS',
				to: (plugins.length ? ' * Plugins: @PLUGINS\n' : '') + 
					(styles.length ? ' * Styles: @STYLES\n' : '')
			}, {
				from: '@MINBUILDPROPS',
				to: plugins[0] !== 'None' || styles[0] !== 'None' ? 
						'(includes: ' + 
							(plugins[0] !== 'None' ? '@PLUGINS' : '') + 
							(styles[0] !== 'None' ? ' / @STYLES' : '') + ') '
						: ''
			}, {
				from: '@STYLES',
				to: styles.length ? styles.join(' ') : ''
			}, {
				from: '@PLUGINS',
				to: plugins.length ? plugins.join(' ') : ''
			}]);

			// Output current build properties
			grunt.log.write("\nBuilding " + "qTip2".green + " "+version+" with " +
				"plugins " + plugins.join(' ').green + " and " +
				"styles "  +styles.join(' ').green + "\n"
			);

			done();
		});
	});

	// Setup all other tasks
	grunt.registerTask('css', 'init clean concat:dist_css mincss:dist replace');
	grunt.registerTask('basic', 'init clean lint concat:basic concat:basic_css min:basic mincss:basic replace');
	grunt.registerTask('default', 'init clean lint concat:dist concat:dist_css min:dist mincss:dist replace');
	grunt.registerTask('dev', 'init clean lint concat min mincss replace');
};