# How to Run Unit Tests in Native PHP

This guide explains different ways to run tests in this PHP project.

## Method 1: Run Tests via Browser (Simplest)

Since your existing test files output HTML, you can run them directly in a browser:

1. **Start XAMPP** (Apache and MySQL)
2. **Access the test files via browser:**
   - `http://localhost/Capstone/test_connection.php`
   - `http://localhost/Capstone/test_dashboard_fetch.php`
   - `http://localhost/Capstone/test_posts.php`
   - `http://localhost/Capstone/test_dues_data.php`

## Method 2: Run Tests via Command Line (PHP CLI)

### Using XAMPP PHP CLI

1. **Navigate to your project directory:**
   ```bash
   cd C:\xampp\htdocs\Capstone
   ```

2. **Run tests using XAMPP's PHP:**
   ```bash
   C:\xampp\php\php.exe test_connection.php
   C:\xampp\php\php.exe test_dashboard_fetch.php
   C:\xampp\php\php.exe test_posts.php
   C:\xampp\php\php.exe test_dues_data.php
   ```

### Add PHP to PATH (Optional)

To use `php` command directly:

1. Add `C:\xampp\php` to your Windows PATH environment variable
2. Then you can run:
   ```bash
   php test_connection.php
   ```

## Method 3: Using PHPUnit (Recommended for Unit Testing)

PHPUnit is the standard testing framework for PHP. Follow these steps:

### Step 1: Install Composer (if not installed)

1. Download Composer from: https://getcomposer.org/download/
2. Install it globally or use the Windows installer

### Step 2: Initialize Composer in Your Project

```bash
cd C:\xampp\htdocs\Capstone
composer init
```

Or use the existing `composer.json` if available.

### Step 3: Install PHPUnit

```bash
composer require --dev phpunit/phpunit
```

### Step 4: Run PHPUnit Tests

```bash
# Run all tests
vendor\bin\phpunit

# Run specific test file
vendor\bin\phpunit tests\ConnectionTest.php

# Run with verbose output
vendor\bin\phpunit --verbose
```

## Method 4: Create a Test Runner Script

Create a simple PHP script to run all tests at once:

```php
<?php
// run_all_tests.php
$tests = [
    'test_connection.php',
    'test_posts.php',
    'test_dues_data.php',
    'test_dashboard_fetch.php'
];

foreach ($tests as $test) {
    echo "\n" . str_repeat('=', 50) . "\n";
    echo "Running: $test\n";
    echo str_repeat('=', 50) . "\n";
    include $test;
    echo "\n";
}
?>
```

Run it:
```bash
C:\xampp\php\php.exe run_all_tests.php
```

## Quick Start Script for Windows

Create a batch file `run_tests.bat`:

```batch
@echo off
echo Running PHP Tests...
echo.

cd /d C:\xampp\htdocs\Capstone

echo Testing Database Connection...
C:\xampp\php\php.exe test_connection.php

echo.
echo Testing Posts...
C:\xampp\php\php.exe test_posts.php

echo.
echo Testing Dues Data...
C:\xampp\php\php.exe test_dues_data.php

echo.
echo All tests completed!
pause
```

## Troubleshooting

1. **PHP not found in command line:**
   - Use full path: `C:\xampp\php\php.exe`
   - Or add PHP to Windows PATH

2. **Database connection errors:**
   - Ensure MySQL is running in XAMPP
   - Check `config.php` for correct database credentials

3. **Tests output HTML instead of text:**
   - Modify test files to output plain text for CLI
   - Or use PHPUnit for proper test framework

## Next Steps

For professional unit testing:
1. Convert existing tests to PHPUnit test cases
2. Create proper test classes in the `tests/` directory
3. Use PHPUnit assertions for better test reporting
4. Set up continuous integration (CI) for automated testing

