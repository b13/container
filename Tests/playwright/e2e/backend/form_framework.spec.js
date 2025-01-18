const {test, expect} = require('@playwright/test');

test.beforeEach(async ({page}) => {
	await page.goto('http://webserver:80/typo3/index.php');
})

test('Form Framework Module loads', async ({page}) => {
	await page.click('[data-modulemenu-identifier="web_FormFormbuilder"]');
	await page.waitForLoadState('networkidle');
	const contentFrame = await page.frameLocator('#typo3-contentIframe');
	expect(await contentFrame.locator('.module-body')).toBeVisible();
	expect(await contentFrame.locator('.module-body > h1')).toHaveText('Form Management');
	// TODO
	// await betterTimes();
});

