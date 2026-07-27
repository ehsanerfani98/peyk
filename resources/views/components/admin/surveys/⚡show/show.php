<?php

use App\Models\SurveyToken;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] #[Title('جزئیات نظرسنجی')] class extends Component
{
    public SurveyToken $survey;

    public function mount(): void
    {
        $this->survey->load('order');
    }
};
