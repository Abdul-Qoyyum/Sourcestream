# Run all tests
./vendor/bin/phpunit

# Run only model tests
./vendor/bin/phpunit tests/Unit/Models/

# Run only feature tests
./vendor/bin/phpunit tests/Feature/

# Run only service tests
./vendor/bin/phpunit tests/Unit/Services/

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage
