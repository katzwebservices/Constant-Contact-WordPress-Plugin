module.exports = function(grunt) {

	// Only need to install one package and this will load them all for you. Run:
	// npm install --save-dev load-grunt-tasks
	require('load-grunt-tasks')(grunt);

	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		less: {
			style: {
				options: {
					compress: true,
					paths: ["css/admin/less"]
				},
				files: {
					'lib/form-designer/css/style.css': 'lib/form-designer/css/less/style.less',
					'css/admin/ctct-admin.css': 'css/admin/less/ctct-admin.less',
					'css/admin/ctct-admin-global.css': 'css/admin/less/ctct-admin-global.less',
				}
			}
		},

		jshint: [
			"js/admin/cc-page.js"
		],

		imagemin: {
			dynamic: {
				files: [{
					options: {
						optimizationLevel: 7
					},
					expand: true,
					cwd: 'assets/images',
					src: ['**/*.{png,jpg,gif}'],
					dest: 'assets/images',
				}]
			}
		},

		uglify: {
			options: {
				mangle: false
			},
			admin: {
				files: [{
					expand: true,
					cwd: 'js/admin',
					src: ['cc-page.js'],
					dest: 'js/admin',
					ext: '.min.js'
				}]
			},
			form_designer: {
				files: [{
					expand: true,
					cwd: 'lib/form-designer/js',
					src: ['cc-code.js'],
					dest: 'lib/form-designer/js',
					ext: '.min.js'
				}]
			}
		},

		watch: {
			scripts: {
				files: ['js/*.js','!js/*.min.js','lib/form-designer/js/*.js','!lib/form-designer/js/*.min.js'],
				tasks: ['uglify','jshint']
			},
			less: {
				files: ['css/admin/less/*.less', 'lib/*/css/less/*.less'],
				tasks: ['less']
			}
		},

		dirs: {
			lang: 'languages'
		},

		// Convert the .po files to .mo files
		potomo: {
			dist: {
				options: {
					poDel: false
				},
				files: [{
					expand: true,
					cwd: '<%= dirs.lang %>',
					src: ['*.po'],
					dest: '<%= dirs.lang %>',
					ext: '.mo',
					nonull: true
				}]
			}
		},

		// Pull in the latest translations
		exec: {
			transifex: 'tx pull -a',

			// Create a ZIP file
			zip: 'git-archive-all ../constant-contact-api.zip'
		},

		// Build translations without POEdit
		makepot: {
			target: {
				options: {
					mainFile: 'constant-contact-api.php',
					type: 'wp-plugin',
					domainPath: '/languages',
					updateTimestamp: false,
					exclude: ['node_modules/.*', 'vendor/.*', 'lib/mail/.*', '.tx/.*' ],
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					processPot: function( pot, options ) {
						pot.headers['language'] = 'en_US';
						pot.headers['language-team'] = 'Katz Web Services, Inc. <support@katz.co>';
						pot.headers['last-translator'] = 'Katz Web Services, Inc. <support@katz.co>';
						pot.headers['report-msgid-bugs-to'] = 'https://github.com/katzwebservices/Constant-Contact-WordPress-Plugin/issues?state=open';

						var translation,
							excluded_meta = [
								'Constant Contact Plugin for WordPress',
								'Powerfully integrate <a href="https://katz.si/6e" target="_blank">Constant Contact</a> into your WordPress website.',
								'https://github.com/katzwebservices/Constant-Contact-WordPress-Plugin',
								'Katz Web Services, Inc.',
								'https://katz.co',
							    'constant-contact-api',
							    '/languages',
							    'GPLv2 or later',
							    'http://www.gnu.org/licenses/gpl-2.0.html'
							];

						for ( translation in pot.translations[''] ) {
							if ( 'undefined' !== typeof pot.translations[''][ translation ].comments.extracted ) {
								if ( excluded_meta.indexOf( pot.translations[''][ translation ].msgid ) >= 0 ) {
									console.log( 'Excluded meta: ' + pot.translations[''][ translation ].msgid );
									delete pot.translations[''][ translation ];
								}
							}
						}

						return pot;
					}
				}
			}
		},

		// Add textdomain to all strings, and modify existing textdomains in included packages.
		addtextdomain: {
			options: {
				textdomain: 'constant-contact-api',    // Project text domain.
				updateDomains: [ 'ctct', 'wp-logging' ]  // List of text domains to replace.
			},
			target: {
				files: {
					src: [
						'*.php',
						'**/*.php',
						'!node_modules/**',
						'!vendor/**',
					]
				}
			}
		}
	});

	// Still have to manually add this one...
	grunt.loadNpmTasks('grunt-wp-i18n');

	// Regular CSS/JS/Image Compression stuff
	grunt.registerTask( 'default', [ 'less', 'uglify', 'jshint', 'imagemin', 'watch' ] );

	// Translation stuff
	grunt.registerTask( 'translate', [ 'exec:transifex', 'potomo', 'addtextdomain', 'makepot' ] );

};
