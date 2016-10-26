###
Build scripts for Fancytree 
###

# jshint directives for the generated JS:

###jshint node: true, unused: false ###

"use strict"

module.exports = (grunt) ->

  grunt.initConfig

    pkg: 
        grunt.file.readJSON("package.json")

    # Project metadata, used by the <banner> directive.
    meta:
        # banner: "/*! <%= pkg.title || pkg.name %> - v<%= pkg.version %> - " +
        banner: "/*! <%= pkg.title || pkg.name %> - @VERSION - @DATE\n" +
                # "<%= grunt.template.today('yyyy-mm-dd HH:mm') %>\n" +
                "<%= pkg.homepage ? '  * ' + pkg.homepage + '\\n' : '' %>" +
                "  * Copyright (c) <%= grunt.template.today('yyyy') %> <%= pkg.author.name %>;" +
                " Licensed <%= _.pluck(pkg.licenses, 'type').join(', ') %> */\n"

    clean:
        build:
            src: [ "build" ]
        dist:
            src: [ "dist" ]
        extMin:
            src: [ "build/jquery.fancytree.*.min.js" ]

    # compress:
    #     dist:
    #         options:
    #             archive: "archive/<%= pkg.name %>-<%= pkg.version %>.zip"
    #         files: [
    #             {expand: true, cwd: "dist/", src: ["**/*"], dest: ""}
    #             ]

    concat:
        core:
            options:
                stripBanners: true
            src: ["<banner:meta.banner>"
                  # "lib/intro.js"
                  "src/<%= pkg.name %>.js"
                  # "lib/outro.js"
                  ]
            dest: "build/<%= pkg.name %>.js"
        all:
            options:
                stripBanners: true
            src: [
                "<%= meta.banner %>"
                # "lib/intro.js"
                "src/jquery.fancytree.js"
                "src/jquery.fancytree.childcounter.js"
                "src/jquery.fancytree.clones.js"
#                "src/jquery.fancytree.columnview.js"
                "src/jquery.fancytree.dnd.js"
                "src/jquery.fancytree.edit.js"
                "src/jquery.fancytree.filter.js"
                "src/jquery.fancytree.glyph.js"
                "src/jquery.fancytree.gridnav.js"
#                "src/jquery.fancytree.menu.js"
                "src/jquery.fancytree.persist.js"
                "src/jquery.fancytree.table.js"
                "src/jquery.fancytree.themeroller.js"
                "src/jquery.fancytree.wide.js"
                # "lib/outro.js"
                ]
            dest: "build/<%= pkg.name %>-all.js"
        custom:
            options:
                banner: "<%= meta.banner %>"
                stripBanners: true
                process: (src, fspec) -> 
                  # Remove all comments, including /*! ... */
                  src = src.replace(/\/\*(.|\n)*\*\//g, "")
                  if /fancytree..+.min.js/.test(fspec)
                    # If it is an extension:
                    # Prepend a one-liner instead
                    fspec = fspec.substr(6) # strip 'build/'
                    src = "\n/*! Extension '" + fspec + "' */" + src
                  return src
            src: [
                "lib/intro.js"
                "build/jquery.fancytree.min.js"
                "build/jquery.fancytree.childcounter.min.js"
                "build/jquery.fancytree.clones.min.js"
#                "build/jquery.fancytree.columnview.min.js"
                "build/jquery.fancytree.dnd.min.js"
                "build/jquery.fancytree.edit.min.js"
                "build/jquery.fancytree.filter.min.js"
                "build/jquery.fancytree.glyph.min.js"
                "build/jquery.fancytree.gridnav.min.js"
#                "build/jquery.fancytree.menu.min.js"
                "build/jquery.fancytree.persist.min.js"
                "build/jquery.fancytree.table.min.js"
                "build/jquery.fancytree.themeroller.min.js"
                "build/jquery.fancytree.wide.min.js"
                "lib/outro.js"
                ]
            dest: "build/<%= pkg.name %>-all.min.js"

    connect:
        forever:
            options:
                port: 8080
                base: "./"
                keepalive: true
        dev: # pass on, so subsequent tasks (like watch) can start
            options:
                port: 8080
                base: "./"
                keepalive: false
        sauce:
            options:
                hostname: "localhost"
                port: 9999
                base: ""
                keepalive: false

    copy:
        build: # copy production files to build folder
            files: [{
                expand: true # required for cwd
                cwd: "src/"
                src: [
                    "skin-**/*.{css,gif,md,png,less}"
                    "skin-common.less"
                    "*.txt"
                    ]
                dest: "build/"
            }, {
                expand: true
                cwd: "src/"
                src: [
                    "jquery.*.js"
                    ]
                # src: [
                #   "skin-**/*.{css,gif,png,less,md}"
                #   "*.txt"
                #   "jquery.*.js"
                #   "skin-common.less"
                #   ]
                dest: "build/src/"
            }, {
                # src: ["*.txt", "*.md"]
                src: ["LICENSE.txt"]
                dest: "build/"
            }]
        dist: # copy build folder to dist
            files: [{expand: true, cwd: "build/", src: ["**"], dest: "dist/"}]

    cssmin:
        options:
          report: "min"
        build:
            expand: true
            cwd: "build/"
            src: ["**/*.fancytree.css", "!*.min.css"]
            dest: "build/"
            ext: ".fancytree.min.css"

    devUpdate:
        main:
            options:
                reportUpdated: true
                updateType: 'prompt'  # 'report'

    docco:
        docs:
            src: ["src/jquery.fancytree.childcounter.js"]
            options:
                output: "doc/annotated-src"

    exec:
        tabfix:
            # Cleanup whitespace according to http://contribute.jquery.org/style-guide/js/
            # (requires https://github.com/mar10/tabfix)
#              cmd: "tabfix -t --line=UNIX -r -m*.js,*.css,*.html,*.json -inode_modules src demo test"
            cmd: "tabfix -t -r -m*.js,*.css,*.html,*.json -inode_modules src demo test"
        upload:
            # FTP upload the demo files (requires https://github.com/mar10/pyftpsync)
            cmd: "pyftpsync --progress upload . ftp://www.wwwendt.de/tech/fancytree --delete-unmatched --omit build,node_modules,.*,_*  -x"
#            cmd: "pyftpsync --progress upload . ftp://www.wwwendt.de/tech/fancytree --omit build,node_modules,.*,_*  -x"

    # htmllint:
    #     all: ["demo/**/*.html", "doc/**/*.html", "test/**/*.html"]

#     jsdoc:
#         build:
#             src: ["src/*.js", "doc/README.md", "doc/jsdoctest.js"]
#             options:
#                 destination: "doc/jsdoc_new"
# #                template: "bin/jsdoc3-moogle",
# #                template: "node_modules/ink-docstrap/template",
#                 template: "../docstrap/template",
#                 configure: "doc/jsdoc.conf.json"
#                 verbose: true

    jshint:
        options:
            # Linting according to http://contribute.jquery.org/style-guide/js/
            jshintrc: ".jshintrc"
        beforeConcat: [
            # "Gruntfile.js"
            "src/*.js"
            "3rd-party/**/jquery.fancytree.*.js"
            "test/unit/*.js"
            "demo/**/*.js"
            ]
        afterConcat: [
            "<%= concat.core.dest %>"
            "<%= concat.all.dest %>"
            ]

    less:
        development:
            options:
#               paths: ["src/"]
#               report: "min"
                compress: false
                yuicompress: false
#               optimization: 10
            files: [
                {expand: true, cwd: "src/", src: "**/ui.fancytree.less", dest: "src/", ext: ".fancytree.css"}
            ]

    qunit:
        build: [ 
            "test/unit/test-core-build.html" 
        ]
        develop: [ 
            "test/unit/test-core.html"
            "test/unit/test-ext-filter.html"
            "test/unit/test-ext-table.html"
            "test/unit/test-ext-misc.html"
        ]

    replace: # grunt-text-replace
        production:
            src: ["build/**/*.js"]
            overwrite : true
            replacements: [ {
                from : /@DATE/g
                to : "<%= grunt.template.today('yyyy-mm-dd\"T\"HH:MM') %>"
            },{
                from : /buildType:\s*\"[a-zA-Z]+\"/g
                to : "buildType: \"production\""
            },{
                from : /debugLevel:\s*[0-9]/g
                to : "debugLevel: 1"
            } ]
        release:
            src: ["dist/**/*.js"]
            overwrite : true
            replacements: [ {
                from : /@VERSION/g
                to : "<%= pkg.version %>"
            } ]

    "saucelabs-qunit":
        all:
            options:
                urls: ["http://localhost:9999/test/unit/test-core.html"]
                # tunnelTimeout: 5
                build: process.env.TRAVIS_JOB_ID
                # concurrency: 3
                throttled: 8
                browsers: [
                  { browserName: "chrome", platform: "Windows 7" }
                  { browserName: "firefox", platform: "Windows 7" }
                  # { browserName: "firefox", platform: "Windows XP" }
                  { browserName: "firefox", platform: "Linux" }
                  { browserName: "internet explorer", version: "6", platform: "Windows XP" }
                  { browserName: "internet explorer", version: "7", platform: "Windows XP" }
                  { browserName: "internet explorer", version: "8", platform: "Windows 7" }
                  { browserName: "internet explorer", version: "9", platform: "Windows 7" }
                  { browserName: "internet explorer", version: "10", platform: "Windows 8" }
                  { browserName: "internet explorer", version: "11", platform: "Windows 8.1" }
                  { browserName: "microsoftedge", platform: "Windows 10" }
                  { browserName: "safari", version: "6", platform: "OS X 10.8" }
                  { browserName: "safari", version: "7", platform: "OS X 10.9" }
                  { browserName: "safari", version: "8", platform: "OS X 10.10" }
                  { browserName: "safari", version: "9", platform: "OS X 10.11" }
                ]
                testname: "fancytree qunit tests"

    uglify:
        # build:
        #     options:
        #         banner: "<%= meta.banner %>"
        #         # preserveComments: "some"
        #         report: "min"
        #         sourceMap: 
        #             (path) -> path.replace(/.js/, ".js.map")
        #         sourceMappingURL: 
        #             (path) -> path.replace(/^build\//, "") + ".map"
        #         sourceMapPrefix: 1 # strip 'build/' from paths

        #     files:
        #         "build/<%= pkg.name %>.min.js": ["<%= concat.core.dest %>"],
        #         "build/<%= pkg.name %>-all.min.js": ["<%= concat.all.dest %>"]

        custom:
            options:  # see https://github.com/gruntjs/grunt-contrib-uglify/issues/366
                report: "min"
                # preserveComments: "some"
                preserveComments: /(?:^!|@(?:license|preserve|cc_on))/
            files: [
              {
                  src: ["**/jquery.fancytree*.js", "!*.min.js"]
                  cwd: "src/"
                  dest: "build/"
                  expand: true
                  rename: (dest, src) ->
                      folder = src.substring(0, src.lastIndexOf("/"))
                      filename = src.substring(src.lastIndexOf("/"), src.length)
                      filename  = filename.substring(0, filename.lastIndexOf("."))
                      return dest + folder + filename + ".min.js"
              }
              ]
        # map_all:
        #     options:
        #         compress: false
        #         mangle: false
        #         sourceMap: true
        #         preserveComments: 'all'
        #     files: [
        #       {
        #           src: 'build/jquery.fancytree-all.min.js'
        #           dest: 'build/jquery.fancytree-all.min.js.map'
        #       }
        #       ]

    watch:
        less:
            files: "src/**/*.less"
            tasks: ["less:development"]
        jshint:
            options:
                atBegin: true
            files: ["src/*.js", "test/unit/*.js", "demo/**/*.js"]
            tasks: ["jshint:beforeConcat"]

    yabs:
        release:
            common: # defaults for all tools
              manifests: ['package.json', 'bower.json']
            # The following tools are run in order:
            run_test: { tasks: ['test'] }
            check: { branch: ['master'], canPush: true, clean: true, cmpVersion: 'gte' }
            bump: {} # 'bump' also uses the increment mode `yabs:release:MODE`
            run_build: { tasks: ['make_release'] }
            commit: { add: '.' }
            tag: {}
            push: { tags: true, useFollowTags: true },
            githubRelease:
              repo: 'mar10/fancytree'
              draft: false
            npmPublish: {}
            bump_develop: { inc: 'prepatch' }
            commit_develop: { message: 'Bump prerelease ({%= version %}) [ci skip]' }
            push_develop: {}

  # ----------------------------------------------------------------------------


  # Load "grunt*" dependencies

  for key of grunt.file.readJSON("package.json").devDependencies
      grunt.loadNpmTasks key  if key isnt "grunt" and key.indexOf("grunt") is 0

  # Register tasks

  grunt.registerTask "server", ["connect:forever"]
  grunt.registerTask "dev", ["connect:dev", "watch"]
  grunt.registerTask "tabfix", ["exec:tabfix"]
  grunt.registerTask "test", [
      "jshint:beforeConcat",
      # "csslint",
      # "htmllint",
      "qunit:develop"
  ]

  grunt.registerTask "sauce", ["connect:sauce", "saucelabs-qunit"]
  if parseInt(process.env.TRAVIS_PULL_REQUEST, 10) > 0
      # saucelab keys do not work on forks
      # http://support.saucelabs.com/entries/25614798
      grunt.registerTask "travis", ["test"]
  else
      grunt.registerTask "travis", ["test", "sauce"]

  grunt.registerTask "default", ["test"]
  grunt.registerTask "ci", ["test"]  # Called by 'npm test'

  grunt.registerTask "build", [
      "less:development"
      "test"
      # "jsdoc:build"
      "docco:docs"
      "clean:build"
      "copy:build"
      "cssmin:build"
      "concat:core"
      "concat:all"
      "uglify:custom"
      "concat:custom"
      "clean:extMin"
      "replace:production"
      "jshint:afterConcat"
      # "uglify:build"
      "qunit:build"
      ]
  
  grunt.registerTask "make_release", [
      "exec:tabfix"
      "build"
      "clean:dist"
      "copy:dist"
      "clean:build"
      "replace:release"
      # "compress:dist"
      ]

  grunt.registerTask "upload", [
      "build"
      "exec:upload"
      ]
