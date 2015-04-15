/*jshint strict:true, browser:true, jquery:true, devel:true, curly:true, eqeqeq:true, immed:true, latedef:true, plusplus:true, undef:true, unused:true, laxbreak:true, nonew:false */
/*global module */

module.exports = function(grunt) {
	"use strict";

	grunt.initConfig({
		compass: {
			dist: {
				options: {
					httpPath: '/',
					sassDir: 'scss',
					cssDir: 'css',
					sourcemap: true,
					watch: true
				}
			}
		}
	});

	grunt.loadNpmTasks('grunt-contrib-compass');

	grunt.registerTask("default", []);
};