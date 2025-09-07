<?php

namespace App\Services;

use App\Models\Operation;
use App\Models\OperationType;
use Dompdf\Dompdf;
use Dompdf\Options;

class CardActivationService
{
    public function getUbaTypes()
    {
        $opType = OperationType::firstWhere('code', 'card_activation');

        $ubaTypes = Operation::where('operation_type_id', $opType->id)
            ->where('status', 'approved')
            ->select('uba_type')
            ->distinct()
            ->orderBy('uba_type')
            ->pluck('uba_type');

        return $ubaTypes;
    }

    public function getPdfCardActivation($op)
    {
        $options = new Options();
        $options->set('defaultFont', 'sans-serif');

        $pdf = new Dompdf($options);

        $html = view('operations.card-activation-export-pdf', ['op' => $op])->render();

        $pdf->loadHtml($html);

        $pdf->setPaper('A4', 'landscape');

        $pdf->render();

        return $pdf;
    }
}
