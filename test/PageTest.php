<?php
require 'vendor/autoload.php';

// https://php-webdriver.github.io/php-webdriver/latest/Facebook/WebDriver.html
// https://gist.github.com/aczietlow/7c4834f79a7afd920d8f

use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverExpectedCondition;
use PHPUnit\Framework\TestCase;

require_once 'WebServerHelper.php';


class WebPageTest extends TestCase
{
	protected $webDriver;

	// Runs once
	public static function setUpBeforeClass(): void
	{
		WebServerHelper::start();
	}

	public static function tearDownAfterClass(): void
	{
		WebServerHelper::stop();
	}

	// Runs before each test
	public function setUp(): void
	{
		// https://github.com/php-webdriver/php-webdriver/wiki/Chrome#start-chromedriver
		// putenv('WEBDRIVER_CHROME_DRIVER=/path/to/chromedriver');

		// https://github.com/php-webdriver/php-webdriver/wiki/Chrome#general-usage
		$chromeOptions = new ChromeOptions();
		// $chromeOptions->setBinary('/home/user/Downloads/my_chrome_binary');
		$chromeOptions->addArguments(['--headless']);

		$capabilities = DesiredCapabilities::chrome();
		$capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);

		$this->webDriver = ChromeDriver::start($capabilities);
	}

	public function tearDown(): void
	{
		$this->webDriver->quit();
	}

	private function elementByCSS($query)
	{
		return $this->webDriver->findElement(WebDriverBy::cssSelector($query));
	}

	public function testPage()
	{
		$this->webDriver->get(WebServerHelper::url());

		sleep(2);
		// Wait for map (Javacript) to load
		$this->webDriver->wait()->until(
			WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::cssSelector('.leaflet-map-pane'))
		);


		$h2 = $this->elementByCSS('h2');
		$this->assertEquals($h2->getText(), 'Level0 OpenStreetMap Editor');


		$input = $this->elementByCSS('input[name="url"]');
		$input->sendKeys(array('w105957600', WebDriverKeys::ENTER));

		sleep(2);

		$textarea = $this->elementByCSS('textarea');
		$this->assertStringContainsString("way 105957600\n", $textarea->getText());
		$this->assertStringContainsString(" addr:street = Downing Street\n", $textarea->getText());


		// Button "Add to Editor"
		$input = $this->elementByCSS('input[name="url"]');
		$input->sendKeys('n3815077900');
		$this->elementByCSS('input[type="submit"][name="add"]')->click();
		sleep(2);
		$textarea = $this->elementByCSS('textarea');
		$this->assertStringContainsString("way 105957600\n", $textarea->getText());
		$this->assertStringContainsString("node 3815077900: ", $textarea->getText());


		// Button "Check for conflicts"
		$this->elementByCSS('input[name="check"]')->click();
		$this->assertStringContainsString('Nothing is modified', $this->webDriver->getPageSource());


		// Button "Show osmChange"
		$this->elementByCSS('input[name="showosc"]')->click();
		sleep(2);
		$this->assertStringContainsString('this will be uploaded to the server', $this->webDriver->getPageSource());
		$this->assertMatchesRegularExpression('/&lt;osmChange [^&]+&gt;\s*&lt;\/osmChange&gt;/', $this->webDriver->getPageSource());
	}
}
