<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function formatCurrency(float $amount): string
{
    return 'Rs. ' . number_format($amount, 2);
}

function statusClass(string $status): string
{
    return match ($status) {
        'PAID',
        'DELIVERED',
        'APPROVED',
        'FULFILLED' => 'status-success',

        'PENDING',
        'PROCESSING',
        'REVIEWING',
        'PACKED',
        'SHIPPED' => 'status-warning',

        'FAILED',
        'CANCELLED',
        'REJECTED' => 'status-danger',

        default => 'status-default'
    };
}
