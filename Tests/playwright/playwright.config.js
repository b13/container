// @ts-check
const {defineConfig, devices} = require('@playwright/test');
const path = require("node:path");
import dotenv from 'dotenv';

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
// Read from GitLab file variable $PLAYWRIGHT_ENV if set. ".env" file otherwise
dotenv.config({ path: process.env.PLAYWRIGHT_ENV || path.resolve(__dirname, '.env') });

/**
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
	testDir: './e2e',
	/* Run tests in files in parallel */
	fullyParallel: true,
	/* Fail the build on CI if you accidentally left test.only in the source code. */
	forbidOnly: !!process.env.CI,
	/* Retry on CI only */
	retries: process.env.CI ? 3 : 0,
	/* Opt out of parallel tests on CI. */
	workers: 1,
	/* Reporter to use. See https://playwright.dev/docs/test-reporters */
	reporter: [
		['html', {open: 'never'}],
		['list', {printSteps: true}],
		['junit'],
	],
	/* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
	use: {
		/* Base URL to use in actions like `await page.goto('/')`. */
		// baseURL: process.env.BASE_URL ? process.env.BASE_URL : 'https://foo.ddev.site',
		baseURL: "http://webserver:80/",
		ignoreHTTPSErrors: true,

		/* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
		trace: 'on-first-retry',
		screenshot: 'on',
	},

	/* Configure projects for major browsers */
	projects: [
		{
			name: 'frontend-cookie',
			testMatch: 'helper/frontend-cookie.setup.js',
		},
		{
			name: 'backend-login',
			testMatch: 'helper/backend-login.setup.js',
		},
		{
			name: 'frontend',
			testMatch: 'frontend/**/*.spec.js',
			dependencies: ['frontend-cookie'],
			use: {
				storageState: path.join(__dirname, '.state/frontend-cookie.json'),
			},
		},
		{
			name: 'backend',
			testMatch: 'backend/**/*.spec.js',
			dependencies: ['backend-login'],
			use: {
				storageState: path.join(__dirname, '.state/backend-login.json'),
			},
		},
	],
});
