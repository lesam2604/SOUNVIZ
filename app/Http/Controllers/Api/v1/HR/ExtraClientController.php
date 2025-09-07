<?php

namespace App\Http\Controllers\Api\v1\HR;

use App\Http\Controllers\Controller;
use App\Models\ExtraClient;
use App\Services\HR\ExtraClientService;
use Illuminate\Http\Request;

class ExtraClientController extends Controller
{
    private $extraClientService;

    public function __construct()
    {
        $this->extraClientService = app(ExtraClientService::class);
    }

    public function fetch(Request $request, ExtraClient $ec)
    {
        return response()->json($ec);
    }

    public function fetchAll(Request $request)
    {
        return response()->json(
            $this->extraClientService->getAllExtraClients()
        );
    }

    protected function validateStoreRequest($request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:191',
            'tin' => 'required|string|max:191',
            'phone_number' => [
                'required',
                'string',
                'max:191',
                function ($attribute, $value, $fail) {
                    $exists = ExtraClient::where('phone_number', $value)
                        ->exists();

                    if ($exists) {
                        return $fail('Ce numéro de téléphone est déjà pris');
                    }
                }
            ],
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            '*.image' => 'Ce champs doit être une image',
            'email.email' => "L'email fournit n'est pas valide",
            'email.confirmed' => "L'email fournit n'a pas ete confirme"
        ]);

        return $data;
    }

    public function store(Request $request)
    {
        $data = $this->validateStoreRequest($request);

        [$error, $ec] = $this->extraClientService
            ->createExtraClient($data, $request->user());

        if ($error) {
            return response()->json($error, 405);
        }

        return response()->json(['message' => "Client extra enregistré avec succès."]);
    }

    protected function validateUpdateRequest($request)
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:191',
            'tin' => 'required|string|max:191',
            'phone_number' => [
                'required',
                'string',
                'max:191',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = ExtraClient::where('phone_number', $value)
                        ->where('id', '<>', $request->extra_client_id)
                        ->exists();

                    if ($exists) {
                        return $fail('Ce numéro de téléphone est déjà pris');
                    }
                }
            ],
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
        ], [
            '*.required' => 'Ce champs est requis',
            '*.string' => 'Ce champs doit être une chaîne de caractères',
            '*.max' => 'La longueur maximale pour ce champs est de 191 caractères',
            '*.image' => 'Ce champs doit être une image',
            'email.email' => "L'email fournit n'est pas valide",
            'email.confirmed' => "L'email fournit n'a pas ete confirme"
        ]);

        return $data;
    }

    public function update(Request $request, ExtraClient $ec)
    {
        $request->merge(['extra_client_id' => $ec->id]);

        $data = $this->validateUpdateRequest($request);

        [$error, $ec] = $this->extraClientService->updateExtraClient(
            $data,
            $ec,
            $request->user()
        );

        if ($error) {
            return response()->json($error, 405);
        }

        return response()->json(['message' => "Client extra mis à jour avec succès."]);
    }

    public function destroy(Request $request, ExtraClient $ec)
    {
        [$error, $success] = $this->extraClientService
            ->deleteExtraClient($ec);

        if ($error) {
            return response()->json($error, 405);
        }

        return response()->json(['message' => "Client extra supprimé."]);
    }

    public function list(Request $request)
    {
        return response()->json(
            $this->extraClientService->getListExtraClients(
                $request->only(['search', 'columns', 'order', 'length', 'start', 'draw'])
            )
        );
    }
}
