<?php

declare(strict_types = 1);

namespace Drupal\Tests\devel_php\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\UserInterface;

/**
 * Tests execute code.
 *
 * @group devel_php
 */
class ExecuteCodeTest extends BrowserTestBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'devel',
    'devel_php',
  ];

  /**
   * The test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Ensure dump output is parseable by tests assertion methods.
    $this->config('devel.settings')
      ->set('devel_dumper', 'default')
      ->save(TRUE);

    $this->user = $this->drupalCreateUser([
      'access devel information',
      'execute php code',
    ]);
  }

  /**
   * Tests handle errors.
   */
  public function testHandleErrors(): void {
    $edit = [];
    $url = Url::fromRoute('devel_php.execute_php');

    $this->drupalLogin($this->user);
    $this->drupalGet($url);

    $edit['code'] = 'devel_help()';
    $this->submitForm($edit, $this->t('Execute'));
    $this->assertSession()->pageTextContains('syntax error, unexpected end of file');

    $edit['code'] = 'devel_help2();';
    $this->submitForm($edit, $this->t('Execute'));
    $this->assertSession()->pageTextContains('Call to undefined function devel_help2()');

    $edit['code'] = 'devel_help();';
    $this->submitForm($edit, $this->t('Execute'));
    $this->assertSession()->pageTextContains('Too few arguments to function devel_help(), 0 passed');
  }

  /**
   * Tests output buffer.
   */
  public function testOutputBuffer(): void {
    $edit = [];
    $url = Url::fromRoute('devel_php.execute_php');

    $this->drupalLogin($this->user);
    $this->drupalGet($url);
    $this->assertSession()->pageTextNotContains(\Drupal::VERSION);

    $edit['code'] = 'echo \Drupal::VERSION;';
    $this->submitForm($edit, $this->t('Execute'));
    $this->assertSession()->pageTextContains(\Drupal::VERSION);
  }

}
