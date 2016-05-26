module.exports = function(grunt) {

    // 1. All configuration goes here

    require('time-grunt')(grunt);

    // Load all plugins using JIT (just in time)
    require('jit-grunt')(grunt, {
        closureCompiler: 'grunt-closure-tools', // for custom tasks.
    });

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        // SASS will compile your Sass files into a CSS file

        sass: {
            dist: {
                files: {
                    'build/css/litework.css': ['sass/bootstrap.scss'],
                }
            },
            nouncss: {
                files: {
                    'sass/no-uncss/no-uncss.css': ['sass/no-uncss/*.scss'],
                }
            }
        },

        // Uncss will remove any un-used CSS in your project, ensuring your CSS doesn't suffer from code bloat

        uncss: {
            dist: {
                options: {
                    ignore: ['#added_at_runtime', '.created_by_jQuery', ':hover', ':active', '.js', '.in', '.wsmenu', '.wsmenu-list', '.wsmenu-submenu', '.overlapblackbg', '.innerpnd', '.typography-text', '.halfdiv', '.menu_form', '.wsmenucontainer', '.wsoffcanvasopener', '.wsmobileheader', '.hometext', 'megacollink', 'megacolimage', 'typographylinks', 'typographydiv', 'mainmapdiv', 'wsmenu-click', 'wsmenu-click02', 'ws-activearrow', 'wsmenu-rotate', 'wsmenu-submenu-sub', 'wsmenu-submenu-sub-sub', 'megamenu', 'ad-style', 'halfmenu', 'animated-arrow', 'callusicon', 'smallogo'],
                    stylesheets: ['/css/litework.css'],
                },
                files: {
                    'build/css/litework.slim.css': ['build/*.html']
                }
            },
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

        // CSSmin will minify your CSS

        cssmin: {
            my_target: {
                files: {
                    'dist/css/litework.prefixed.slim.concat.min.css': ['build/css/litework.prefixed.slim.concat.css'],
                }
            }
        },

        // Concat will concantenate (join) any files specified - Javascript in this case

        concat: {
            dist: {
                files: {
                    'build/scripts/litework.js': ['scripts/*.js']
                }
            },
            css: {
                files: {
                    'build/css/litework.prefixed.slim.concat.css': ['sass/no-uncss/no-uncss.css', 'build/css/litework.prefixed.slim.css']
                }
            },
        },

        // Uglify will minify any Javascript specified - beforehand be sure to concat where possible!

        uglify: {
            my_target: {
                files: {
                    'build/scripts/litework.min.js': ['build/scripts/litework.js']
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
                            replacement: '<%= grunt.file.read("snippets/footer/twitter-share.html") %>'
                        }, {
                            match: 'twitter-follow',
                            replacement: '<%= grunt.file.read("snippets/footer/twitter-follow.html") %>'
                        }, {
                            match: 'facebook-like',
                            replacement: '<%= grunt.file.read("snippets/footer/facebook-like.html") %>'
                        }, {
                            match: 'contactform',
                            replacement: '<%= grunt.file.read("snippets/contactform.html") %>'
                        }, {
                            match: 'gplus',
                            replacement: '<%= grunt.file.read("snippets/footer/gplus.html") %>'
                        }, {
                            match: 'pinit',
                            replacement: '<%= grunt.file.read("snippets/footer/pinit.html") %>'
                        }, {
                            match: 'linkedin-share',
                            replacement: '<%= grunt.file.read("snippets/footer/linkedin-share.html") %>'
                        },

                    ]
                },
                files: [{
                    expand: true,
                    flatten: true,
                    src: ['snippets/footer/footer.html'],
                    dest: 'snippets/'
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
                    }, {
                        match: 'contactform',
                        replacement: '<%= grunt.file.read("snippets/contactform.html") %>'
                    }, {
                        match: 'webmaster',
                        replacement: '<%= grunt.file.read("snippets/webmaster.html") %>'
                    }, {
                        match: 'isotope',
                        replacement: '<%= grunt.file.read("snippets/isotope.html") %>'
                    }, {
                        match: 'homepage',
                        replacement: '<%= pkg.homepage %>'
                    }, {
                        match: 'inlineJS',
                        replacement: '<%= grunt.file.read("build/scripts/litework.min.js") %>'
                    }]
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
                            match: 'inline-css',
                            replacement: '<%= grunt.file.read("dist/css/litework.prefixed.slim.concat.min.css") %>'
                        },

                    ]
                },
                files: [{
                    expand: true,
                    flatten: true,
                    src: ['build/*.html'],
                    dest: 'build/inline/'
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
                    'style-disabled': false,
                    'force': true,
                    'doctype-html5': true,
                    'img-alt-require': true,
                    'tag-self-close': true
                },
                cwd: 'build/inline/',
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
                    cwd: 'build/inline',
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
                server: {
                    baseDir: "./dist"
                }
            }
        },

        // Sitemap - This task will create an xml sitemap based on the HTML files in your dist folder

        sitemap: {
            xml: {
                siteRoot: 'dist/'
            }
        },

        // Copy will copy certain files into the dist/ folder, in this case the robots.txt and your .htaccess file

        copy: {
            main: {
                files: [{
                    expand: true,
                    src: ['robots.txt'],
                    dest: 'dist/',
                }, {
                    expand: true,
                    src: ['.htaccess'],
                    dest: 'dist/',
                }, {
                    expand: true,
                    src: ['fonts/**'],
                    dest: 'dist/',
                }, {
                    expand: true,
                    src: ['*cfg*/**'],
                    dest: 'dist/',
                }]
            }
        },

        // Clean - This plugin will "clean" folders passed to it

        clean: ['dist', 'build']

    });

    // Here we tell Grunt what to do when we type 'grunt' into the CLI
    grunt.registerTask('default', ["sass:dist", "sass:nouncss", "replace:footer", "concat:dist", "uglify", "replace:dist", "uncss", "autoprefixer", 'concat:css', "cssmin", "imagemin", "replace:css", "htmlhint", "htmlmin", "sitemap", "copy"]);

    // This will start a live preview of your project and then trigger the watch task
    grunt.registerTask('live', ["browserSync"]);

    // This will clean the dist and build folders prior to running our full grunt task
    grunt.registerTask('fresh', ["clean", "default"]);
};
