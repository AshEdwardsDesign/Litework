module.exports = function (grunt) {

	// 1. All configuration goes here 

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		concat: {
			// 2. Configuration for concatinating files goes here.
			dist: {
				src: [
            'scripts/html5shiv.js',
            'scripts/respond.min.js'
        ],
				dest: 'dist/scripts/ie.js',
			},

			dist: {
				src: [
            'scripts/jquery-1.11.1.min.js',
            'scripts/jquery.slicknav.min.js'
        ],
				dest: 'dist/scripts/litework.js',
			}
		},

		uglify: {
			build: {
				src: 'dist/scripts/ie.js',
				dest: 'dist/scripts/ie.min.js',
			},

			build: {
				src: 'dist/scripts/litework.js',
				dest: 'dist/scripts/litework.min.js',
			}
		},

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

		htmlmin: {
			dev: {
				files: [{
					expand: true,
					cwd: '',
					src: '*.html',
					dest: 'dist/'
            }]
			}
		}

	});

	// 3. Where we tell Grunt we plan to use this plug-in.
	grunt.loadNpmTasks("grunt-contrib-concat");
	grunt.loadNpmTasks("grunt-contrib-uglify");
	grunt.loadNpmTasks("grunt-contrib-imagemin");
	grunt.loadNpmTasks("grunt-contrib-htmlmin");

	// 4. Where we tell Grunt what to do when we type "grunt" into the terminal.
	grunt.registerTask('default', ["concat", "uglify", "imagemin", "htmlmin", ]);

};