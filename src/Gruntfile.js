module.exports = function (grunt) {
    'use strict';

    var env = grunt.option('env') || 'prod';
    grunt.config('env', env);
    console.log('Environment: ' + env);

    grunt.config('locales', [
        'uk',
        'en',
        'ru'
    ]);

    grunt.initConfig({
        jshint: {
            files: [],
            options: {
                loopfunc: true,
                globals: {
                    jQuery: true,
                    console: true,
                    module: true
                }
            }
        },
        jade: {
            components: {
                options: {
                    client: true,
                    debug: grunt.config('env') !== 'prod',
                    compileDebug: grunt.config('env') !== 'prod',
                    processName: function(filename) {
                        var path = require('path');
                        return path.basename(filename, '.jade');
                    }
                },
                files: {
                    "Resources/public/js/components.jade.js": [
                        "Resources/assets/components/**/*.jade",
                    ]
                }
            }
        },
        uglify: {
            messages: {
                files: (function() {
                    var files = {}, locale;
                    grunt.config('locales').forEach(function(locale) {
                        files['Resources/public/js/messages.' + locale + '.js'] = [
                            'Resources/assets/components/*/messages.' + locale + '.js'
                        ];
                    });
                    return files;
                })()
            },
            perfectScrollbar: {
                files: {
                    'Resources/public/js/perfect-scrollbar.js': [
                        'node_modules/perfect-scrollbar/dist/js/perfect-scrollbar.jquery.min.js'
                    ]
                }
            }
        },
        watch: {
            project: {
                files: [
                    'Resources/assets/**/*'
                ],
                tasks: ['build'],
                options: {},
            }
        },
        cssmin: {
            perfectScrollbarCss: {
                files: {
                    'Resources/public/css/perfect-scrollbar.css': [
                        'node_modules/perfect-scrollbar/dist/css/perfect-scrollbar.min.css'
                    ]
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-jade');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-newer');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('build', [
        'newer:jade',
        'newer:uglify'
    ]);

    grunt.registerTask('listen', [
        'watch'
    ]);

    grunt.registerTask('default', [
        'build'
    ]);
};