<?php
// FILE: /tests/functional_tests.php

/**
 * SplashMarket - Functional Tests
 *
 * Basic functional tests for core features
 * PHP 7.0+ compatible
 */

// Load configuration and classes
require_once __DIR__ . '/../config/config.php';

// Autoloader
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load helpers
require_once APP_PATH . '/helpers/functions.php';
require_once APP_PATH . '/helpers/security.php';

echo "SplashMarket Functional Tests\n";
echo "=============================\n\n";

$passed = 0;
$failed = 0;

// Test 1: Database Connection
echo "Test 1: Database Connection... ";
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    if ($conn) {
        echo "PASSED\n";
        $passed++;
    } else {
        echo "FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 2: User Model
echo "Test 2: User Model Operations... ";
try {
    $userModel = new User();
    $user = $userModel->findByEmail('admin@splashmarket.com');
    if ($user && $user['role'] === 'platform_admin') {
        echo "PASSED\n";
        $passed++;
    } else {
        echo "FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 3: Tenant Model
echo "Test 3: Tenant Model Operations... ";
try {
    $tenantModel = new Tenant();
    $tenants = $tenantModel->getActive();
    if (is_array($tenants) && count($tenants) > 0) {
        echo "PASSED\n";
        $passed++;
    } else {
        echo "FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 4: Product Model
echo "Test 4: Product Model Operations... ";
try {
    $productModel = new Product();
    $products = $productModel->findAll([], null, 5);
    if (is_array($products)) {
        echo "PASSED\n";
        $passed++;
    } else {
        echo "FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 5: Order Model
echo "Test 5: Order Model Operations... ";
try {
    $orderModel = new Order();
    $orders = $orderModel->findAll([], 'created_at DESC', 5);
    if (is_array($orders)) {
        echo "PASSED\n";
        $passed++;
    } else {
        echo "FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 6: Subscription Model
echo "Test 6: Subscription Model Operations... ";
try {
    $subscriptionModel = new Subscription();
    $subscription = $subscriptionModel->getActiveSubscription(1);
    if ($subscription && isset($subscription['plan_id'])) {
        echo "PASSED\n";
        $passed++;
    } else {
        echo "FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 7: Password Hashing
echo "Test 7: Password Hashing... ";
try {
    $password = 'testpassword123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    if (password_verify($password, $hash)) {
        echo "PASSED\n";
        $passed++;
    } else {
        echo "FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 8: Validator
echo "Test 8: Input Validation... ";
try {
    $validator = new Validator(['email' => 'test@example.com', 'name' => 'Test']);
    $validator->required('name')->email('email');
    if ($validator->passes()) {
        echo "PASSED\n";
        $passed++;
    } else {
        echo "FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 9: Coupon Validation
echo "Test 9: Coupon Validation... ";
try {
    $couponModel = new Coupon();
    $coupon = $couponModel->findByCode('WELCOME10', 1);
    if ($coupon) {
        $validation = $couponModel->validateCoupon('WELCOME10', 1, 100.00);
        if ($validation['valid']) {
            echo "PASSED\n";
            $passed++;
        } else {
            echo "FAILED\n";
            $failed++;
        }
    } else {
        echo "FAILED (coupon not found)\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 10: Helper Functions
echo "Test 10: Helper Functions... ";
try {
    $escaped = e('<script>alert("xss")</script>');
    $money = money(99.99);
    $sanitized = sanitize('<b>Test</b>');
    if (strpos($escaped, '&lt;script&gt;') !== false && strpos($money, '99.99') !== false) {
        echo "PASSED\n";
        $passed++;
    } else {
        echo "FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Summary
echo "\n=============================\n";
echo "Test Results:\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total:  " . ($passed + $failed) . "\n";
echo "=============================\n";

if ($failed === 0) {
    echo "\n✓ All tests passed!\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed.\n";
    exit(1);
}
