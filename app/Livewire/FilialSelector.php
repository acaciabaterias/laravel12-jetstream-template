<?php

namespace App\Livewire;

use App\Models\Filial;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class FilialSelector extends Component
{
    public $selectedFilial;

    public function mount()
    {
        $this->selectedFilial = session('filial_id');
    }

    public function updatedSelectedFilial($value)
    {
        if (auth()->user()->isSuperAdmin()) {
            Session::put('filial_id', $value);

            return redirect()->route('dashboard');
        }
    }

    public function render()
    {
        if (! auth()->user() || ! auth()->user()->isSuperAdmin()) {
            return <<<'blade'
                <div></div>
            blade;
        }

        $filiais = Filial::all();

        return view('livewire.filial-selector', [
            'filiais' => $filiais,
        ]);
    }
}
