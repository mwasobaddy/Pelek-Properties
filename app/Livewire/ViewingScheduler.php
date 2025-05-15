<?php

namespace App\Livewire;

use App\Models\Property;
use App\Services\SalePropertyService;
use Livewire\Component;

class ViewingScheduler extends Component
{
    public Property $property;
    public $appointmentData = [
        'client_name' => '',
        'client_phone' => '',
        'client_email' => '',
        'appointment_date' => '',
        'notes' => ''
    ];

    public function mount(Property $property)
    {
        $this->property = $property;
    }

    public function scheduleViewing()
    {
        $this->validate([
            'appointmentData.client_name' => 'required|string|max:255',
            'appointmentData.client_phone' => 'required|string|max:20',
            'appointmentData.client_email' => 'nullable|email|max:255',
            'appointmentData.appointment_date' => 'required|date|after:now',
            'appointmentData.notes' => 'nullable|string|max:1000',
        ]);

        $salePropertyService = app(SalePropertyService::class);
        $appointment = $salePropertyService->scheduleViewing(
            $this->property,
            $this->appointmentData
        );

        $this->reset('appointmentData');
        $this->dispatch('viewing-scheduled', $appointment->id);
    }

    public function render()
    {
        return view('livewire.viewing-scheduler');
    }
}
