@extends('layouts.app')

@section('pageContent')
<div class="container mt-4">
    <h2>Gestion des demandes d'annulation d'opérations</h2>
    <div class="card mt-3">
        <div class="card-body">
            @if($requests->isEmpty())
                <p>Aucune demande d'annulation pour le moment.</p>
            @else
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Demandée par</th>
                            <th>Partenaire</th>
                            <th>Opération</th>
                            <th>Montant</th>
                            <th>Frais</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                        <tr id="request-{{ $req->id }}">
                            <td>{{ $req->id }}</td>
                            <td>{{ optional($req->requester)->first_name }} {{ optional($req->requester)->last_name }}</td>
                            <td>{{ optional(optional($req->operation->partner)->user)->first_name }} {{ optional(optional($req->operation->partner)->user)->last_name }}</td>
                            <td>{{ optional($req->operation->operationType)->name }} ({{ $req->operation->code }})</td>
                            @php
                                $op = $req->operation;
                                $opCode = optional($op->operationType)->code;
                                if ($opCode === 'account_recharge') {
                                    // Montant réel pour recharge de compte; frais = 0
                                    $amountVal = (float) ($op->data->trans_amount ?? 0);
                                    $senderType = $op->data->sender_phone_number_type ?? '';
                                    $feeVal    = ($senderType === 'MomoPay') ? round($amountVal * (0.005 / 0.995)) : 0;
                                } elseif ($opCode === 'balance_withdrawal') {
                                    // Montant et frais (2%) pour retrait de solde
                                    $amountVal = (float) ($op->data->amount ?? 0);
                                    $feeVal    = $amountVal * 0.02;
                                } else {
                                    // Montant et frais pour les autres opérations
                                    $amountVal = (float) ($op->amount ?? 0);
                                    $feeVal    = (float) ($op->fee ?? 0);
                                    if ((float)$feeVal === 0.0 && $op->operationType) {
                                        $opTypeObj = $op->operationType;
                                        $amountField = $opTypeObj->amount_field ?? null;
                                        $baseAmount = $amountField ? (float) ($op->data->{$amountField} ?? 0) : $amountVal;
                                        $cardType = $op->data->card_type ?? null;
                                        $master = optional($op->partner)->getMaster();

                                        if ($baseAmount > 0 && $master) {
                                            if ($master->hasCommissions($opTypeObj->id, $cardType)) {
                                                [$tmp, $calcFee] = $opTypeObj->getFee($baseAmount, $cardType);
                                                $feeVal = (float) $calcFee;
                                            } else {
                                                $feeVal = ($opTypeObj->code === 'card_recharge') ? 0 : ($baseAmount <= 500000 ? 100 : 200);
                                            }
                                            if ($amountVal === 0.0) { $amountVal = $baseAmount; }
                                        }
                                    }
                                }
                            @endphp
                            <td>{{ number_format($amountVal, 0, ',', ' ') }} FCFA</td>
                            <td>{{ number_format($feeVal, 0, ',', ' ') }} FCFA</td>
                            <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($req->status === 'pending')
                                    <span class="badge bg-warning text-dark">En attente</span>
                                @elseif($req->status === 'approved')
                                    <span class="badge bg-success">Approuvée</span>
                                @else
                                    <span class="badge bg-danger">Rejetée</span>
                                @endif
                            </td>
                            <td>
                                @if($req->status === 'pending')
                                    <button class="btn btn-sm btn-success approve-btn" data-id="{{ $req->id }}">Approuver</button>
                                    <button class="btn btn-sm btn-danger reject-btn" data-id="{{ $req->id }}">Rejeter</button>
                                @else
                                    <span>-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection

@section('jsPlugins')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
function showToast(message, type = 'info') {
    const colors = { info: '#435ebe', success: '#4fbe87', error: '#ef5350' };
    Toastify({
        text: message,
        duration: 4000,
        close: true,
        gravity: 'top',
        position: 'right',
        backgroundColor: colors[type] || colors.info
    }).showToast();
}

// Approuver
document.querySelectorAll('.approve-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        axios.post(`/admin/operations-cancel/approve/${id}`)
            .then(response => {
                if (response.data.ok) {
                    showToast(response.data.message, 'success');
                    // maj statut (col 8)
                    (function(){
                        const el = document.querySelector(`#request-${id} td:nth-child(8) span`);
                        if (el) {
                            el.className = 'badge bg-success';
                            el.innerText = 'Approuvee';
                        }
                    })();
                    document.querySelector(`#request-${id} td:nth-child(8) span`).className = 'badge bg-success';
                    document.querySelector(`#request-${id} td:nth-child(7) span`).innerText = 'Approuvée';
                    document.querySelector(`#request-${id} td:nth-child(9)`).innerHTML = '-';
                } else {
                    showToast(response.data.message, 'error');
                }
            })
            .catch(() => showToast('Erreur serveur', 'error'));
    });
});

// Rejeter
document.querySelectorAll('.reject-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        axios.post(`/admin/operations-cancel/reject/${id}`)
            .then(response => {
                if (response.data.ok) {
                    showToast(response.data.message, 'success');
                    // maj statut (col 8)
                    (function(){
                        const el = document.querySelector(`#request-${id} td:nth-child(8) span`);
                        if (el) {
                            el.className = 'badge bg-danger';
                            el.innerText = 'Rejetee';
                        }
                    })();
                    document.querySelector(`#request-${id} td:nth-child(7) span`).className = 'badge bg-danger';
                    document.querySelector(`#request-${id} td:nth-child(7) span`).innerText = 'Rejetée';
                    document.querySelector(`#request-${id} td:nth-child(9)`).innerHTML = '-';
                } else {
                    showToast(response.data.message, 'error');
                }
            })
            .catch(() => showToast('Erreur serveur', 'error'));
    });
});
</script>
@endsection
