<?php

namespace App\Http\Controllers;

use App\Models\Prodotto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProdottoController extends Controller
{
    public function index(Request $request)
    {
        $query = Prodotto::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'ilike', "%{$search}%")
                  ->orWhere('codice_prodotto', 'ilike', "%{$search}%");
            });
        }

        $prodotti = $query->orderBy('codice_prodotto')->paginate(25)->withQueryString();

        return Inertia::render('Prodotti/Index', [
            'prodotti' => $prodotti,
            'filters'  => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Prodotti/Form', ['prodotto' => null]);
    }

    public function store(Request $request)
    {
        Prodotto::create($this->validated($request));

        return redirect()->route('prodotti.index')->with('success', 'Prodotto creato.');
    }

    public function edit(Prodotto $prodotto)
    {
        return Inertia::render('Prodotti/Form', ['prodotto' => $prodotto]);
    }

    public function update(Request $request, Prodotto $prodotto)
    {
        $prodotto->update($this->validated($request, $prodotto->id));

        return redirect()->route('prodotti.index')->with('success', 'Prodotto aggiornato.');
    }

    public function destroy(Prodotto $prodotto)
    {
        $prodotto->delete();

        return redirect()->route('prodotti.index')->with('success', 'Prodotto eliminato.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'codice_prodotto' => ['required', 'string', 'max:20',
                \Illuminate\Validation\Rule::unique('prodotti', 'codice_prodotto')->ignore($ignoreId)],
            'nome'            => ['required', 'string', 'max:200'],
            'pezzatura_valore' => ['nullable', 'numeric', 'min:0'],
            'pezzatura_um'    => ['nullable', 'string', 'max:10'],
            'attivo'          => ['boolean'],
            'note'            => ['nullable', 'string'],
        ]);
    }
}
