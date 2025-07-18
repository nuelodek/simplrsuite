  <?php

  require_once __DIR__ . '/../../vendor/autoload.php';

  use Sentry\SentrySdk;
  use function Sentry\init;

  class SentryInitializer {
      public function initialize() {
          init([
              'dsn' => 'https://bd57a327b8c04ca2bc2907391b50147a@o4508098840821760.ingest.us.sentry.io/4508132099817472',
              'traces_sample_rate' => 1.0,
              'profiles_sample_rate' => 1.0,
          ]);

          try {
              $this->functionThatMightFail();
          } catch (\Throwable $exception) {
              SentrySdk::getCurrentHub()->captureException($exception);
          }
      }

      private function functionThatMightFail() {
          // Implement the actual functionality here
          // This is a placeholder for the function that might throw an exception
          throw new \Exception("This is a test exception");
      }
  }

  // Usage
  $initializer = new SentryInitializer();
  $initializer->initialize();