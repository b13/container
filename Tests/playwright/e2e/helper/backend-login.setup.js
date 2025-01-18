import {test as setup, expect} from '@playwright/test';

setup('Backend login', async ({page}) => {
	//await page.goto('/typo3/index.php');
	await page.goto('http://webserver:80/typo3/index.php');
	await page.fill('#t3-username', process.env.BE_USER || 'admin');
	await page.fill('#t3-password', process.env.BE_USER_PASSWORD || 'Password.1');
	await page.waitForTimeout(500);
	await page.click('#t3-login-submit');
	await page.waitForLoadState('networkidle');
	await expect(await page.locator('.topbar-header-site-title')).toBeVisible();

	await page.context().storageState({path: './.state/backend-login.json'});
});
