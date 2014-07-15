'use strict';

module.exports = function(grunt) {

	var export_dir = '../wp/wp-content/plugins';
	var export_multisite_dir = '../wpmu/wp-content/plugins';

	// Project configuration.
	grunt.initConfig({

		// Load grunt project configuration
		pkg: grunt.file.readJSON('package.json'),

		sync: {
			main: {
				files: [
					{
						cwd: 'good-reviews-wp/',
						src: '**',
						dest: export_dir + '/<%= pkg.name %>'
					},
					{
						cwd: 'good-reviews-wp/',
						src: '**',
						dest: export_multisite_dir + '/<%= pkg.name %>'
					}
				]
			}
		},

		// Watch for changes on some files and auto-compile them
		watch: {
			sync: {
				files: ['good-reviews-wp/**'],
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
