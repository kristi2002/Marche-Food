<?php

namespace App\Http\Controllers;

use App\Models\Acquisto;
use App\Models\AcquistoRiga;
use App\Models\Fornitore;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcquistoController extends Controller
{
    public function index(Request $request)
    {
        $query = Acquisto::with('fornitore')
            ->withCount('righe');

        if ($search = $request->input('search')) {
            $query->where('numero_documento', 'ilike', "%{$search}%");
        }

        if ($fornitoreId = $request->input('fornitore_id')) {
            $query->where('fornitore_id', $fornitoreId);
        }

        if ($da = $request->input('da')) {
            $query->whereDate('data_documento', '>=', $da);
        }

        if ($a = $request->input('a')) {
            $query->whereDate('data_documento', '<=', $a);
        }

        $acquisti = $query->orderByDesc('data_documento')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $fornitori = Fornitore::where('attivo', true)
            ->orderBy('ragione_sociale')
            ->get(['id', 'ragione_sociale', 'codice']);

        return Inertia::render('Acquisti/Index', [
            'acquisti' => $acquisti,
            'fornitori' => $fornitori,
            'filters'  => $request->only(['search', 'fornitore_id', 'da', 'a']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Acquisti/Form', [
            'acquisto' => null,
            'fornitori' => Fornitore::where('tipo', 'alimentare')
                ->where('attivo', true)
                ->orderBy('ragione_sociale')
                ->get(['id', 'ragione_sociale', 'codice']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request);

        $acquisto = Acquisto::create([
            'fornitore_id'    => $data['fornitore_id'],
            'numero_documento' => $data['numero_documento'],
            'data_documento'  => $data['data_documento'],
            'tipo_documento'  => $data['tipo_documento'],
            'note'            => $data['note'] ?? null,
        ]);

        foreach ($data['righe'] as $riga) {
            $acquisto->righe()->create($riga);
        }

        return redirect()->route('acquisti.index')
            ->with('success', 'Acquisto registrato con successo.');
    }

    public function edit(Acquisto $acquisto)
    {
        $acquisto->load('righe');

        return Inertia::render('Acquisti/Form', [
            'acquisto' => $acquisto,
            'fornitori' => Fornitore::where('tipo', 'alimentare')
                ->where('attivo', true)
                ->orderBy('ragione_sociale')
                ->get(['id', 'ragione_sociale', 'codice']),
        ]);
    }

    public function update(Request $request, Acquisto $acquisto)
    {
        $data = $this->validateRequest($request);

        $acquisto->update([
            'fornitore_id'    => $data['fornitore_id'],
            'numero_documento' => $data['numero_documento'],
            'data_documento'  => $data['data_documento'],
            'tipo_documento'  => $data['tipo_documento'],
            'note'            => $data['note'] ?? null,
        ]);

        $acquisto->righe()->delete();
        foreach ($data['righe'] as $riga) {
            $acquisto->righe()->create($riga);
        }

        return redirect()->route('acquisti.index')
            ->with('success', 'Acquisto aggiornato.');
    }

    public function destroy(Acquisto $acquisto)
    {
        $acquisto->delete();

        return redirect()->route('acquisti.index')
            ->with('success', 'Acquisto eliminato.');
    }

    private function validateRequest(Request $request): array
    {
        return $request->validate([
            'fornitore_id'       => ['required', 'exists:fornitori,id'],
            'numero_documento'   => ['required', 'string', 'max:50'],
            'data_documento'     => ['required', 'date'],
            'tipo_documento'     => ['required', 'in:DDT,Fattura,Bolla'],
            'note'               => ['nullable', 'string'],
            'righe'              => ['required', 'array', 'min:1'],
            'righe.*.nome_prodotto' => ['required', 'string', 'max:200'],
            'righe.*.um'         => ['nullable', 'string', 'max:10'],
            'righe.*.quantita_pz' => ['nullable', 'numeric', 'min:0'],
            'righe.*.quantita_kg' => ['required', 'numeric', 'min:0.001'],
            'righe.*.lotto'      => ['nullable', 'string', 'max:100'],
            'righe.*.lotto_esterno' => ['nullable', 'string', 'max:100'],
            'righe.*.scadenza'          => ['nullable', 'date'],
            'righe.*.data_in'           => ['required', 'date'],
            'righe.*.data_out'          => ['nullable', 'date'],
            'righe.*.nota_credito_ref'  => ['nullable', 'string', 'max:50'],
        ]);
    }

    public function print(Acquisto $acquisto)
    {
        $acquisto->load(['fornitore', 'righe']);

        return Inertia::render('Acquisti/Print', ['acquisto' => $acquisto]);
    }
}
