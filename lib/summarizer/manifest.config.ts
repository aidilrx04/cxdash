import { defineManifest } from '@crxjs/vite-plugin';
import packageJson from './package.json' with { type: 'json' };
const { version } = packageJson;


// Convert from Semver (example: 0.1.0-beta6)
const [major, minor, patch, label = '0'] = version
	// can only contain digits, dots, or dash
	.replace(/[^\d.-]+/g, '')
	// split into version parts
	.split(/[.-]/);

export default defineManifest({
	manifest_version: 3,
	name: 'CXSummarizer',
	version: `${major}.${minor}.${patch}.${label}`,
	version_name: version,
	action: {
		default_popup: 'index.html',
	},
	permissions: [
		"tabs",
		"scripting",
		"storage",
		"unlimitedStorage"
	],
	host_permissions: [
		"http://localhost:8000/*",
		"https://localhost:8000/*",
		"https://notebook.google.com/*",
		"https://chat.deepseek.com/*",
		"https://pgtraining-admin.peopleaps.com/*"
	],
	externally_connectable: {
		matches: [
			"http://localhost:8000/*",
			"http://localhost/*",
			"http://127.0.0.1/*",
			"http://paps.local:5123/*"
		]
	},
	background: {
		service_worker: 'src/service-worker.ts'
	},
	content_scripts: [
		{
			"matches": [
				"http://localhost:8000/*"
			],
			"js": [
				"src/content-origin.ts"
			]
		},
		{
			"matches": [
				"https://notebook.google.com/*"
			],
			"js": [
				"src/content-notebook.ts"
			]
		},
		{
			"matches": [
				"https://chat.deepseek.com/*"
			],
			"js": [
				"src/content-deepseek.ts"
			]
		},
		{
			"matches": [
				"https://pgtraining-admin.peopleaps.com/*"
			],
			"js": [
				"src/content-paps.ts"
			]
		},
		{
			"matches": [
				"https://pgtraining-admin.peopleaps.com/*"
			],
			"js": [
				"src/interceptor.iife.ts",
			],
			run_at: "document_start",
			world: 'MAIN'
		}
	]
});
