module.exports = function (grunt) {

	// 1. All configuration goes here 

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// SASS will compile your Sass files into CSS files

		sass: {
			dist: {
				files: {
					'css/litework.css': ['sass/litework.scss']
				}
			}
		},

		// Autoprefixer adds vendor prefixes to the CSS file - in this case it checks against browsers with >1% global usage

		autoprefixer: {
			options: {
				browsers: ['> 1%']
			},
			your_target: {
				src: 'css/litework.css',
				dest: 'css/litework.css'
			},
		},

		// CSSmin will minify your CSS

		cssmin: {
			my_target: {
				files: {
					'css/litework.min.css': ['css/litework.css']
				}
			}
		},

		// Concat will concantenate (join) any files specified - Javascript in this case

		concat: {
			dist: {
				files: {
					'scripts/ie.js': ['scripts/html5shiv.js', 'scripts/respond.min.js'],
					'scripts/litework.js': ['scripts/jquery-1.11.1.min.js', 'scripts/jquery.slicknav.min.js']
				}
			}
		},

		// Uglify will minify any Javascript specified - beforehand be sure to concat where possible!

		uglify: {
			my_target: {
				files: {

					'scripts/ie.min.js': ['scripts/ie.js'],
					'scripts/litework.min.js': ['scripts/litework.js']
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
					dest: 'dist/img/',
        }]
			}
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
				cwd: '',
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
					cwd: '',
					src: '*.html',
					dest: 'dist/'
            }]
			}
		},

		// Copy stuff goes here - this is set to copy production-ready files from the css and script folders into the appropriate place in the dist folder

		copy: {
			main: {
				files: {
					'dist/scripts/ie.min.js': ['scripts/ie.min.js'], // IE bandaid js
					'dist/scripts/litework.min.js': ['scripts/litework.min.js'], // Liteworks' main js file
					'dist/css/litework.min.css': ['css/litework.min.css'] // Liteworks' CSS file
				}

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
				},
			}
		},
		// WATCH stuff goes here

		watch: {
			html: {
				files: ['*.html'],
				tasks: ['htmlhint', 'htmlmin']
			},
			sass: {
				files: ['sass/*.scss'],
				tasks: ['sass']
			},
			autoprefixer: {
				files: ['css/litework.css'],
				tasks: ['autoprefixer', 'cssmin']
			},
			js: {
				files: ['scripts/*.js'],
				tasks: ['concat', 'uglify']
			},
			img: {
				files: ['img/**/*.{png,jpg,gif}'],
				tasks: ['imagemin']
			},
			copy: {
				files: ['scripts/ie.min.js', 'scripts/litework.min.js', 'css/litework.min.css'],
				tasks: ['copy']
			}
		},

	});

	// List of used plugins
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-autoprefixer');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-imagemin');
	grunt.loadNpmTasks('grunt-contrib-htmlmin');
	grunt.loadNpmTasks('grunt-htmlhint');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-browser-sync');
	grunt.loadNpmTasks('grunt-replace');

	// Here we tell Grunt what to do when we type 'grunt' into the CLI
	grunt.registerTask('default', ["sass", "autoprefixer", "cssmin", "concat", "uglify", "imagemin", "htmlhint", "htmlmin", "copy"]);
	grunt.registerTask('live', ["browserSync", "watch"]);
};