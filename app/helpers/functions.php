<?php
// FILE: /app/helpers/functions.php

/**
 * SplashMarket - Global Helper Functions
 *
 * Common utility functions used throughout the application
 * PHP 7.0+ compatible
 */

/**
 * Escape HTML output
 *
 * @param string $string String to escape
 * @return string
 */
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get base URL
 *
 * @param string $path Optional path to append
 * @return string
 */
function baseUrl($path = '')
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

    return $protocol . '://' . $host . $basePath . '/' . ltrim($path, '/');
}

/**
 * Get asset URL
 *
 * @param string $path Asset path
 * @return string
 */
function asset($path)
{
    return baseUrl('assets/' . ltrim($path, '/'));
}

/**
 * Get upload URL
 *
 * @param string|null $path Upload path
 * @return string
 */
function uploadUrl($path)
{
    if (!$path) {
        return asset('images/placeholder.png');
    }

    $uploadsDir = __DIR__ . '/../../storage/uploads/';
    $uploadsUrl = baseUrl('../storage/uploads/');

    return $uploadsUrl . ltrim($path, '/');
}

/**
 * Format money
 *
 * @param float $amount Amount
 * @param string $currency Currency code
 * @return string
 */
function money($amount, $currency = 'USD')
{
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£'
    ];

    $symbol = isset($symbols[$currency]) ? $symbols[$currency] : $currency . ' ';

    return $symbol . number_format($amount, 2);
}

/**
 * Format date
 *
 * @param string $date Date string
 * @param string $format Format string
 * @return string
 */
function formatDate($date, $format = 'M d, Y')
{
    if (!$date) {
        return '';
    }

    return date($format, strtotime($date));
}

/**
 * Format datetime
 *
 * @param string $datetime Datetime string
 * @param string $format Format string
 * @return string
 */
function formatDatetime($datetime, $format = 'M d, Y H:i')
{
    if (!$datetime) {
        return '';
    }

    return date($format, strtotime($datetime));
}

/**
 * Get time ago string
 *
 * @param string $datetime Datetime string
 * @return string
 */
function timeAgo($datetime)
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Truncate string
 *
 * @param string $string String to truncate
 * @param int $length Max length
 * @param string $append String to append
 * @return string
 */
function truncate($string, $length = 100, $append = '...')
{
    if (strlen($string) <= $length) {
        return $string;
    }

    return substr($string, 0, $length) . $append;
}

/**
 * Get status badge HTML
 *
 * @param string $status Status string
 * @return string
 */
function statusBadge($status)
{
    $classes = [
        'pending' => 'badge-warning',
        'confirmed' => 'badge-info',
        'processing' => 'badge-primary',
        'shipped' => 'badge-success',
        'completed' => 'badge-success',
        'canceled' => 'badge-danger',
        'refunded' => 'badge-secondary',
        'draft' => 'badge-secondary',
        'published' => 'badge-success',
        'active' => 'badge-success',
        'inactive' => 'badge-secondary',
        'paid' => 'badge-success',
        'failed' => 'badge-danger'
    ];

    $class = isset($classes[$status]) ? $classes[$status] : 'badge-secondary';

    return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
}

/**
 * Generate pagination HTML
 *
 * @param array $pagination Pagination data
 * @param string $baseUrl Base URL for links
 * @return string
 */
function paginate($pagination, $baseUrl)
{
    if ($pagination['totalPages'] <= 1) {
        return '';
    }

    $html = '<nav><ul class="pagination">';

    // Previous button
    if ($pagination['page'] > 1) {
        $prevUrl = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . ($pagination['page'] - 1);
        $html .= '<li class="page-item"><a class="page-link" href="' . $prevUrl . '">Previous</a></li>';
    }

    // Page numbers
    for ($i = 1; $i <= $pagination['totalPages']; $i++) {
        if ($i == $pagination['page']) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $pageUrl = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . $i;
            $html .= '<li class="page-item"><a class="page-link" href="' . $pageUrl . '">' . $i . '</a></li>';
        }
    }

    // Next button
    if ($pagination['page'] < $pagination['totalPages']) {
        $nextUrl = $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . ($pagination['page'] + 1);
        $html .= '<li class="page-item"><a class="page-link" href="' . $nextUrl . '">Next</a></li>';
    }

    $html .= '</ul></nav>';

    return $html;
}

/**
 * Redirect helper
 *
 * @param string $url URL to redirect to
 */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/**
 * Get old input value (for form repopulation)
 *
 * @param string $key Input key
 * @param mixed $default Default value
 * @return mixed
 */
function old($key, $default = '')
{
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

/**
 * Check if value is selected (for select inputs)
 *
 * @param mixed $value Value to check
 * @param mixed $selected Selected value
 * @return string
 */
function selected($value, $selected)
{
    return $value == $selected ? 'selected' : '';
}

/**
 * Check if value is checked (for checkboxes/radios)
 *
 * @param mixed $value Value to check
 * @param mixed $checked Checked value
 * @return string
 */
function checked($value, $checked)
{
    return $value == $checked ? 'checked' : '';
}

/**
 * Debug dump and die
 *
 * @param mixed $data Data to dump
 */
function dd($data)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

/**
 * Sanitize string for output
 *
 * @param string $string String to sanitize
 * @return string
 */
function sanitize($string)
{
    return htmlspecialchars(strip_tags($string), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF field HTML
 *
 * @return string
 */
function csrf_field()
{
    $token = Session::getCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Calculate percentage
 *
 * @param float $value Current value
 * @param float $total Total value
 * @return float
 */
function percentage($value, $total)
{
    if ($total == 0) {
        return 0;
    }

    return round(($value / $total) * 100, 2);
}
