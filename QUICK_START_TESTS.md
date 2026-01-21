# Quick Start: Running Tests

## 🚀 Fastest Way: Run Tests via Browser

1. **Start XAMPP** (Apache + MySQL)
2. **Open browser and visit:**
   - `http://localhost/Capstone/test_connection.php`
   - `http://localhost/Capstone/test_posts.php`
   - `http://localhost/Capstone/test_dues_data.php`
   - `http://localhost/Capstone/test_dashboard_fetch.php`

## 🖥️ Command Line: Run All Tests at Once

### Option 1: Using the Batch File (Windows)
```bash
# Double-click or run:
run_tests.bat
```

### Option 2: Manual Command Line
```bash
# Navigate to project
cd C:\xampp\htdocs\Capstone

# Run individual tests
C:\xampp\php\php.exe test_connection.php
C:\xampp\php\php.exe test_posts.php
C:\xampp\php\php.exe test_dues_data.php
C:\xampp\php\php.exe test_dashboard_fetch.php
```

## 🧪 Professional Testing: Using PHPUnit

### Step 1: Install Composer
Download from: https://getcomposer.org/download/

### Step 2: Install PHPUnit
```bash
cd C:\xampp\htdocs\Capstone
composer install
```

### Step 3: Run PHPUnit Tests
```bash
# Run all tests
vendor\bin\phpunit

# Run specific test
vendor\bin\phpunit tests\ConnectionTest.php

# Run with verbose output
vendor\bin\phpunit --verbose
```

## 📋 Test Files Overview

| File | Description | Method |
|------|-------------|--------|
| `test_connection.php` | Tests database connection | Browser/CLI |
| `test_posts.php` | Tests posts table | Browser/CLI |
| `test_dues_data.php` | Tests dues calculation | Browser/CLI |
| `test_dashboard_fetch.php` | Tests dashboard APIs | Browser/CLI |
| `tests/ConnectionTest.php` | PHPUnit database tests | PHPUnit |
| `tests/PostsTest.php` | PHPUnit posts tests | PHPUnit |
| `tests/DuesDataTest.php` | PHPUnit dues tests | PHPUnit |

## 🔧 Troubleshooting

### PHP not found in command line
- Use full path: `C:\xampp\php\php.exe`
- Or add `C:\xampp\php` to Windows PATH

### Database connection errors
- Ensure MySQL is running in XAMPP
- Check `config.php` for correct credentials
- Verify database `capstone_db` exists

### Composer not found
- Install Composer from https://getcomposer.org/
- Or use browser/CLI method instead

## 📝 Next Steps

1. **For quick testing:** Use browser method
2. **For automated testing:** Use batch file
3. **For professional testing:** Use PHPUnit

