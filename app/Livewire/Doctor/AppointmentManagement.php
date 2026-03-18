<?php

namespace App\Livewire\Doctor;

use App\Models\Consultation;
use App\Models\Doctor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AppointmentManagement extends Component
{
    use WithPagination;

    public $status = 'all';
    public $dateFilter = 'all';
    public $search = '';

    protected $queryString = ['status', 'search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function setStatus($status)
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function render()
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();

        $query = Consultation::with('patient')
            ->where(function($q) use ($doctor) {
                $q->where('doctor_id', $doctor?->id);
            });

        if ($this->status !== 'all') {
            $query->where(function($q) {
                $q->where('status', $this->status);
            });
        }

        if ($this->search) {
            $query->whereHas('patient', function($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('uhid', 'like', '%' . $this->search . '%');
            });
        }

        // Upcoming = Today and Future
        if ($this->status === 'Upcoming') {
            $query->where(function($q) {
                $q->where('consultation_date', '>=', today());
            });
        }

        $appointments = $query->latest('consultation_date')->paginate(10);

        return view('livewire.doctor.appointment-management', compact('appointments'));
    }
}
