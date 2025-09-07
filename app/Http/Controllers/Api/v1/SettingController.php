<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\OperationType;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function get(Request $request)
    {
        $dashboardMessage = Setting::first()->dashboard_message;
        $opTypes = OperationType::orderBy('position')->get();

        return response()->json(compact('dashboardMessage', 'opTypes'));
    }

    protected function opTypeDataValidator($opTypeData)
    {
        foreach (['fees', 'commissions'] as $valueType) {
            if (
                !isset($opTypeData->$valueType) ||
                !is_array($opTypeData->$valueType) ||
                empty($opTypeData->$valueType)
            ) {
                return "{$valueType} array can not be empty";
            }

            foreach ($opTypeData->$valueType as $step) {
                if (
                    !is_object($step) ||
                    empty(get_object_vars($step))
                ) {
                    return "{$valueType} steps objects can not be empty";
                }

                if (
                    isset($step->breakpoint) &&
                    is_string($step->breakpoint) &&
                    (is_numeric($step->breakpoint) || $step->breakpoint === '')
                ) {
                    $step->breakpoint = strval($step->breakpoint);
                } else {
                    return "All {$valueType} steps breakpoints must be a valid number or an empty string";
                }

                if (
                    isset($step->value) &&
                    is_string($step->value) &&
                    preg_match('/^\d+([.,]\d+)?(%?)$/', $step->value)
                ) {
                    $step->value = strval($step->value);
                } else {
                    return "All {$valueType} steps values must be a valid number or a percentage";
                }
            }
        }

        return $opTypeData;
    }

    public function updateDashboardMessage(Request $request)
    {
        $data = $request->validate([
            'dashboard_message' => 'nullable|string|max:1000'
        ]);

        $reviewer = $request->user();

        DB::beginTransaction();

        try {
            Setting::first()->update([
                'dashboard_message' => $data['dashboard_message'],
                'updator_id' => $reviewer->id
            ]);

            History::create([
                'user_id' => $reviewer->id,
                'title' => 'Message du tableau de bord mis a jour.',
                'content' => 'Vous avez mis a jour le message du tableau de bord.'
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Message du tableau de bord sauvegardé']);
    }

    public function set(Request $request)
    {
        $data = $request->validate([
            'operation_type_id' => 'required|numeric|exists:operation_types,id',
            'card_type' => 'nullable|string|exists:card_types,name'
        ]);

        $opTypeData = $this->opTypeDataValidator(json_decode($request->op_type_data ?? '[]'));

        if (is_string($opTypeData)) {
            return response()->json(['message' => $opTypeData], 422);
        }

        $cardType = $data['card_type'] ?? null;

        $authUser = $request->user();

        DB::beginTransaction();

        try {
            $opType = OperationType::find($data['operation_type_id']);

            $fees = $opType->fees;
            $commissions = $opType->commissions;

            $fees->$cardType = $opTypeData->fees;
            $commissions->$cardType = $opTypeData->commissions;

            $opType->fees = $fees;
            $opType->commissions = $commissions;

            $opType->save();

            History::create([
                'user_id' => $authUser->id,
                'title' => "Paramètres mis a jour pour {$opType->name}" .
                    ($cardType ? " ({$cardType})" : ''),
                'content' => 'Vous avez mis a jour les paramètres de ' . $opType->name .
                    ($cardType ? " ({$cardType})" : '')
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Paramètres sauvegardés']);
    }
}
