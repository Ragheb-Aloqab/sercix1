<?php

namespace App\View\Components\Company;

use Illuminate\View\Component;

class Glass extends Component
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
        return view('components.company.glass');
    }
}
