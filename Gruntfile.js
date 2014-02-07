'use strict';

module.exports = function(grunt) {

	var export_dir = '../wp/wp-content/plugins';

	// Project configuration.
	grunt.initConfig({

		// Load grunt project configuration
		pkg: grunt.file.readJSON('package.json'),

		// Configure less CSS compiler
		less: {
			build: {
				options: {
					compress: true,
					cleancss: true,
					ieCompat: true
				},
				files: {
					'good-reviews/assets/css/style.css': [
						'good-reviews/assets/src/less/style.less',
						'good-reviews/assets/src/less/style-*.less'
					]
				}
			}
		},

		// Configure JSHint
		jshint: {
			test: {
				src: 'good-reviews/assets/src/js/*.js'
			}
		},

		// Concatenate scripts
		concat: {
			build: {
				files: {
					'good-reviews/assets/js/frontend.js': [
						'good-reviews/assets/src/js/frontend.js',
						'good-reviews/assets/src/js/frontend-*.js'
					]
				}
			}
		},

		// Minimize scripts
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
			},
			build: {
				files: {
					'good-reviews/assets/js/frontend.js' : 'good-reviews/assets/src/js/frontend.js',
					'good-reviews/assets/js/admin.js' : 'good-reviews/assets/src/js/admin.js'
				}
			}
		},

		sync: {
			main: {
				files: [
					{
						cwd: 'good-reviews/',
						src: '**',
						dest: export_dir + '/<%= pkg.name %>'
					}
				]
			}
		},

		// Watch for changes on some files and auto-compile them
		watch: {
			less: {
				files: ['good-reviews/assets/src/less/*.less'],
				tasks: ['less', 'sync']
			},
			js: {
				files: ['good-reviews/assets/src/js/*.js'],
				tasks: ['jshint', 'concat', 'uglify', 'sync']
			},
			sync: {
				files: ['!good-reviews/**/*.less', '!good-reviews/**/*.css', '!good-reviews/**/*.js', 'good-reviews/**'],
				tasks: ['sync']
			}
		}

	});

	// Load tasks
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-nodeunit');
	grunt.loadNpmTasks('grunt-sync');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');

	// Default task(s).
	grunt.registerTask('default', ['watch']);

};
