<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo       = 'todo';
    case InProgress = 'in_progress';
    case Done       = 'done';

    public function label(): string
    {
        return match($this) {
            self::Todo       => 'To do',
            self::InProgress => 'In progress',
            self::Done       => 'Done',
        };
    }

    /**
     * Tailwind class for the status badge.
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::Todo       => 'bg-gray-100 text-gray-600',
            self::InProgress => 'bg-blue-100 text-blue-700',
            self::Done       => 'bg-green-100 text-green-700',
        };
    }
}
