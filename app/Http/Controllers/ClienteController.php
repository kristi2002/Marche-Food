<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ragione_sociale', 'ilike', "%{$search}%")
                  ->orWhere('codice_cliente', 'ilike', "%{$search}%");
            });
        }

        if ($request->input('solo_attivi')) {
            $query->where('attivo', true);
        }

        $clienti = $query->orderBy('ragione_sociale')->paginate(25)->withQueryString();

        return Inertia::render('Clienti/Index', [
            'clienti' => $clienti,
            'filters' => $request->only(['search', 'solo_attivi']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Clienti/Form', ['cliente' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Cliente::create($data);

        return redirect()->route('clienti.index')->with('success', 'Cliente creato con successo.');
    }

    public function edit(Cliente $cliente)
    {
        return Inertia::render('Clienti/Form', ['cliente' => $cliente]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $this->validated($request, $cliente->id);
        $cliente->update($data);

        return redirect()->route('clienti.index')->with('success', 'Cliente aggiornato.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clienti.index')->with('success', 'Cliente eliminato.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'codice_cliente'  => ['required', 'string', 'max:20',
                \Illuminate\Validation\Rule::unique('clienti', 'codice_cliente')->ignore($ignoreId)],
            'ragione_sociale' => ['required', 'string', 'max:200'],
            'piva'            => ['nullable', 'string', 'max:20'],
            'indirizzo'       => ['nullable', 'string'],
            'email'           => ['nullable', 'email', 'max:100'],
            'telefono'        => ['nullable', 'string', 'max:30'],
            'attivo'          => ['boolean'],
            'note'            => ['nullable', 'string'],
        ]);
    }
}
