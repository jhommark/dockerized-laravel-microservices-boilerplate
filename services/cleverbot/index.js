const express = require('express');
const compression = require('compression');
const puppeteer = require('puppeteer');

const app = express();
const port = process.env.PORT || 8080;

app.use(express.json());
app.use(compression());

app.get('/', async (req, res) => {
  try {
    if (!req.query.message) res.json({
      status: 'failed',
      data: 'No message sent'
    });

    const browser = await puppeteer.launch({ args: ['--no-sandbox'] });
    const page = await browser.newPage();

    page.setDefaultNavigationTimeout(0);
    page.goto('https://www.cleverbot.com/');

    await page.waitForSelector('#avatarform', { timeout: 0 });
    await page.type('input[name="stimulus"]', req.query.message.toString());
    await page.keyboard.press('Enter');
    await page.waitForSelector('#snipTextIcon', { timeout: 0 });

    const response = await page.evaluate(() => {
      window.stop();
      return {
        status: 'success',
        data: document.querySelector('#line1 .bot').innerHTML,
      }
    });

    res.json(response);

    await browser.close();
  } catch(error) {
    console.error(error);
  }
});

app.listen(port, () => console.log(`Server listening on port ${port}!`));
