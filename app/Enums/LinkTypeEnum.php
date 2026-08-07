<?php

namespace App\Enums;

enum LinkTypeEnum: string
{
    case Code = 'code';
    case Text = 'text';
    case Link = 'link';
    case Utm = 'utm';
    case Iframe = 'iframe';

    public function label(): string
    {
        return __('app.shortener.types.'.$this->value);
    }
}
