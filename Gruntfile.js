module.exports = function (grunt) {

	// 1. All configuration goes here 

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		// Concat will concantenate (join) any files specified - Javascript in this case
		
		concat: {
			dist: {
				files: {
					'dist/scripts/ie.js': ['scripts/html5shiv.js', 'scripts/respond.min.js'],
					'dist/scripts/litework.js': ['scripts/jquery-1.11.1.min.js', 'scripts/jquery.slicknav.min.js']
				}
			}
		},

		// Uglify will minify any Javascript specified - beforehand be sure to concat where possible!

		uglify: {
			my_target: {
				files: {

					'dist/scripts/ie.min.js': ['dist/scripts/ie.js'],
					'dist/scripts/litework.min.js': ['dist/scripts/litework.js']
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

		// HTML Min for minifying all HTML in the root - set to remove both comments and whitespace

		htmlmin: {
			dist: {
				options: {
					removeComments: true,
					collapseWhitespace: true
				},
				files: [{
					expand: true,
					cwd: '',
					src: '*.html',
					dest: 'dist/'
            }]
			}
		}

		// Put SASS here
		
		// Put Autoprefixer here
		
		// WATCH stuff goes here

	});

	// List of used plugins
	grunt.loadNpmTasks("grunt-contrib-concat");
	grunt.loadNpmTasks("grunt-contrib-uglify");
	grunt.loadNpmTasks("grunt-contrib-imagemin");
	grunt.loadNpmTasks("grunt-contrib-htmlmin");

	// Here we tell Grunt what to do when we type 'grunt' into the CLI
	grunt.registerTask('default', ["concat", "uglify", "imagemin", "htmlmin"]);

};