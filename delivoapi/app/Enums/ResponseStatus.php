<?php

namespace App\Enums;

enum ResponseStatus: string
{
    case Success = 'success';
    case Error = 'error';

    public function toBool(): bool
    {
        return $this === self::Success;
    }

    public function defaultMessage(): string
    {
        return match ($this) {
            self::Success => 'Operation completed successfully.',
            self::Error => 'An error occurred.',
        };
    }
}
