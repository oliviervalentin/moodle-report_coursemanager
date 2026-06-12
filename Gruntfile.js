/**
 * Gruntfile for report_coursemanager.
 *
 * Minifies all AMD source files from amd/src/ into amd/build/.
 *
 * Usage:
 *   npm install
 *   npx grunt           # build all
 *   npx grunt watch     # rebuild on save
 */
'use strict';

module.exports = function(grunt) {

    // Auto-detect all .js files in amd/src/.
    var srcFiles = grunt.file.expand({cwd: 'amd/src'}, ['*.js']);
    var uglifyFiles = {};
    srcFiles.forEach(function(file) {
        uglifyFiles['amd/build/' + file.replace('.js', '.min.js')] = ['amd/src/' + file];
    });

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        uglify: {
            options: {
                banner: '// This file is part of Moodle - https://moodle.org/\n' +
                        '// Auto-generated — do not edit. Edit amd/src/ instead.\n',
                sourceMap: true,
                compress: {
                    drop_console: false,
                },
            },
            amd: {
                files: uglifyFiles,
            },
        },

        watch: {
            amd: {
                files: ['amd/src/*.js'],
                tasks: ['uglify:amd'],
                options: {spawn: false},
            },
        },
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['uglify:amd']);
    grunt.registerTask('amd',     ['uglify:amd']);
};
