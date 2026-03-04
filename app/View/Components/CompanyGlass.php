<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CompanyGlass extends Component
{
    public function __construct(
        public string $title = ''
    ) {
        if ($title === '') {
            $this->title = __('dashboard.subtitle_default');
        }
    }

    public function render()
    {
        return view('components.company-glass');
    }
}
