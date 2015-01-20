module.exports = function(grunt) {

  // 1. All configuration goes here

  require('time-grunt')(grunt);

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    // SASS will compile your Sass files into a CSS file

    sass: {
      dist: {
        files: {
          'build/css/litework.css': ['sass/litework.scss']
        }
      }
    },

    // Autoprefixer adds vendor prefixes to the CSS file - in this case it checks against browsers with >1% global usage

    autoprefixer: {
      options: {
        browsers: ['> 1%']
      },
      your_target: {
        src: 'build/css/litework.slim.css',
        dest: 'build/css/litework.prefixed.slim.css'
      }
    },

    // Uncss will remove any un-used CSS in your project, ensuring your CSS doesn't suffer from code bloat

    uncss: {
      dist: {
        options: {
          ignore: ['#added_at_runtime', '.created_by_jQuery', ':hover', ':active'],
          stylesheets: ['css/litework.css'],
        },
        files: {
          'build/css/litework.slim.css': ['build/*.html']
        }
      },
    },

    // CSSmin will minify your CSS

    cssmin: {
      my_target: {
        files: {
          'dist/css/litework.prefixed.slim.min.css': ['build/css/litework.prefixed.slim.css']
        }
      }
    },

    // Concat will concantenate (join) any files specified - Javascript in this case

    concat: {
      dist: {
        files: {
          'build/scripts/ie.js': ['scripts/ie/*.js'],
          'build/scripts/litework.js': ['scripts/*.js']
        }
      }
    },

    // Uglify will minify any Javascript specified - beforehand be sure to concat where possible!

    uglify: {
      my_target: {
        files: {

          'dist/scripts/ie.min.js': ['build/scripts/ie.js'],
          'dist/scripts/litework.min.js': ['build/scripts/litework.js']
        }
      }
    },

    // Image min will compress all images in the "img/" source folder with PNG, JPG or GIF extensions

    imagemin: {
      dynamic: {
        files: [{
          expand: true,
          cwd: 'img/',
          src: ['**/*.{png,jpg,gif}'],
          dest: 'dist/img/'
        }]
      }
    },

    // Replace will take the @@code snippets in your HTML files and replace them with the relevant code from the snippets folder

    replace: {
      footer: {
        options: {
          patterns: [{
            match: 'twitter-share',
            replacement: '<%= grunt.file.read("snippets/twitter-share.html") %>'
          }, {
            match: 'twitter-follow',
            replacement: '<%= grunt.file.read("snippets/twitter-follow.html") %>'
          }, {
            match: 'facebook-like',
            replacement: '<%= grunt.file.read("snippets/facebook-like.html") %>'
          },]
        },
        files: [{
          expand: true,
          flatten: true,
          src: ['snippets/footer-build.html'],
          dest: 'snippets/footer.html'
        }]
      },
      dist: {
        options: {
          patterns: [{
            match: 'nav',
            replacement: '<%= grunt.file.read("snippets/nav.html") %>'
          }, {
            match: 'footer',
            replacement: '<%= grunt.file.read("snippets/footer.html") %>'
          }, {
            match: 'warnings',
            replacement: '<%= grunt.file.read("snippets/warnings.html") %>'
          }, {
            match: 'analytics',
            replacement: '<%= grunt.file.read("snippets/analytics.html") %>'
          }, {
            match: 'facebookSDK',
            replacement: '<%= grunt.file.read("snippets/facebookSDK.html") %>'
          }, ]
        },
        files: [{
          expand: true,
          flatten: true,
          src: ['*.html'],
          dest: 'build/'
        }]
      },
      css: {
        options: {
          patterns: [{
            match: 'nav',
            replacement: '<%= grunt.file.read("snippets/nav.html") %>'
          }, {
            match: 'footer',
            replacement: '<%= grunt.file.read("snippets/footer.html") %>'
          }, {
            match: 'warnings',
            replacement: '<%= grunt.file.read("snippets/warnings.html") %>'
          }, {
            match: 'css',
            replacement: '<%= grunt.file.read("dist/css/litework.prefixed.slim.min.css") %>'
          }]
        },
        files: [{
          expand: true,
          flatten: true,
          src: ['index.html'],
          dest: 'build/'
        }]
      },
    },

    // HTMLhint will check all HTML in the root folder for errors

    htmlhint: {
      html1: {
        options: {
          'tag-pair': true,
          'tagname-lowercase': true,
          'attr-lowercase': true,
          'attr-value-double-quotes': true,
          'doctype-first': true,
          'spec-char-escape': true,
          'id-unique': true,
          'head-script-disabled': true,
          'style-disabled': true,
          'force': true,
          'doctype-html5': true,
          'img-alt-require': true,
          'tag-self-close': true
        },
        cwd: 'build/',
        src: ['*.html']
      }
    },

    // HTML Min for minifying all HTML in the root - set to remove both comments and whitespace

    htmlmin: {
      dist: {
        options: {
          removeComments: true,
          collapseWhitespace: true,
          minifyJS: true
        },
        files: [{
          expand: true,
          cwd: 'build/',
          src: '*.html',
          dest: 'dist/'
        }]
      }
    },

    // Browser-Sync will allow a live-preview of the project across your browsers & devices

    browserSync: {
      bsFiles: {
        src: ['dist/css/*.css', 'dist/*.html', 'dist/scripts/*.js']
      },
      options: {
        watchTask: true,
        server: {
          baseDir: "./dist"
        }
      }
    },

    // Sitemap - This task will create an xml sitemap based on the HTML files in your dist folder

    sitemap: {
      xml: {
        siteRoot: ['dist/']
      }
    },

    'ftp-deploy': {
      build: {
        auth: {
          host: 'ftp.ashedwardsdesign.co.uk',
          port: 21,
          authKey: 'key1'
        },
        src: 'dist/',
        dest: 'public_html/projects/litework'
      }
    },

    // WATCH stuff goes here

    watch: {
      html: {
        files: ['*.html', 'snippets/*.html'],
        tasks: ['replace:dist', 'htmlhint', 'replace:css', 'htmlmin', 'sitemap']
      },
      sass: {
        files: ['sass/*.scss'],
        tasks: ['sass']
      },
      css: {
        files: ['build/css/litework.css'],
        tasks: ['uncss', 'autoprefixer', 'cssmin', 'replace:css', 'watch:html']
      },
      js: {
        files: ['scripts/*.js'],
        tasks: ['concat', 'uglify']
      },
      img: {
        files: ['img/**/*.{png,jpg,gif}'],
        tasks: ['imagemin']
      }
    },

    // Clean - This plugin will "clean" folders passed to it

    clean: ['dist', 'build']

  });

  // Load all plugins using matchdep
  require('matchdep').filterDev('grunt-*').forEach(grunt.loadNpmTasks);

  // Here we tell Grunt what to do when we type 'grunt' into the CLI
  grunt.registerTask('default', ["sass", "replace:dist", "uncss", "autoprefixer", "cssmin", "concat", "uglify", "imagemin", "htmlhint", "replace:css", "htmlmin", "sitemap"]);

  // This will start a live preview of your project and then trigger the watch task
  grunt.registerTask('live', ["browserSync", "watch"]);

  // This will clean the dist and build folders prior to running our full grunt task
  grunt.registerTask('fresh', ["clean", "default"]);

  // This will deploy your dist/ folder to your server, auth is contained in the separate .ftppass file
  grunt.registerTask('ftp', ['ftp-deploy']);
};
