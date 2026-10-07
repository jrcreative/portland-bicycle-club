const defaults = require( '@wordpress/scripts/config/eslint.config.cjs' );
const wordpress = require( '@wordpress/eslint-plugin' );
const globals = require( 'globals' );

module.exports = [
	...defaults,
	{
		ignores: [
			'tests/e2e/test-results/**',
			'assets/**',
			'build/**',
			'deploy/**',
			'vendor/**',
		],
	},
	...wordpress.configs[ 'test-playwright' ].map( ( config ) => ( {
		...config,
		files: [ 'tests/e2e/specs/**/*.js' ],
	} ) ),
	...wordpress.configs[ 'test-playwright' ].map( ( config ) => ( {
		...config,
		files: [ 'tests/e2e/global-*.js', 'tests/e2e/utils/**/*.js' ],
	} ) ),
	{
		files: [ '*.config.js', 'tests/e2e/**/*.js' ],
		ignores: [ 'tests/e2e/specs/**' ],
		languageOptions: {
			globals: {
				...globals.node,
				// Remove browser globals inherited from the shared preset.
				window: 'off',
				document: 'off',
				SCRIPT_DEBUG: 'off',
				wp: 'off',
			},
		},
	},
	{
		files: [ 'src/js/**/*.js' ],
		languageOptions: {
			globals: {
				Element: 'readonly',
				XMLHttpRequest: 'readonly',
				alert: 'readonly',
				jQuery: 'readonly',
			},
		},
		settings: {
			// WordPress and webpack provide these browser dependencies.
			'import/core-modules': [ 'jquery', 'backbone', 'imagesloaded' ],
		},
	},
	{
		files: [ 'src/js/admin-*.js' ],
		languageOptions: {
			globals: { wcBoxOfficeParams: 'readonly', tinyMCE: 'readonly' },
		},
	},
	{
		files: [ 'src/js/frontend.js' ],
		languageOptions: {
			globals: { wc_box_office: 'readonly', onScan: 'readonly' },
		},
		rules: {
			// Preserve the localized public settings object.
			camelcase: [ 'error', { allow: [ 'wc_box_office' ] } ],
		},
	},
	{
		files: [ 'src/js/**/*.js' ],
		rules: {
			// Classic jQuery scripts are formatted only: no semantic rewrites.
			'no-var': 'off',
			'object-shorthand': 'off',
			yoda: 'off',
			'@typescript-eslint/no-this-alias': 'off',
		},
	},
	{
		files: [ 'src/admin/blocks/**/*.js' ],
		settings: {
			// The editor provides these dependencies through WordPress script handles.
			'import/core-modules': [
				'@wordpress/i18n',
				'@wordpress/components',
				'@wordpress/block-editor',
				'@wordpress/blocks',
				'@wordpress/element',
			],
		},
	},
	{
		files: [ 'src/admin/blocks/scan-ticket/edit.js' ],
		languageOptions: {
			globals: { wcboScanTicketBlockData: 'readonly' },
		},
	},
];
