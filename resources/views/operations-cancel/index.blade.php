@extends('layouts.app')

@section('cssPlugins')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
@endsection

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
                            <th>Collaborateur</th>
                            <th>Partenaire</th>
                            <th>Opération</th>
                            <th>Montant</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                        <tr id="request-{{ $req->id }}">
                            <td>{{ $req->id }}</td>
                            <td>{{ $req->user->first_name ?? 'N/A' }} {{ $req->user->last_name ?? '' }}</td>
                            <td>{{ $req->operation->partner->first_name ?? 'N/A' }} {{ $req->operation->partner->last_name ?? '' }}</td>
                            <td>{{ $req->operation->type ?? 'N/A' }}</td>
                            <td>{{ number_format($req->amount, 0, ',', ' ') }} FCFA</td>
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
    const bgColor = {
        info: '#435ebe',
        success: '#4fbe87',
        error: '#ef5350'
    }[type] || '#435ebe';
    Toastify({
        text: message,
        duration: 4000,
        close: true,
        gravity: 'top',
        position: 'right',
        backgroundColor: bgColor
    }).showToast();
}

// Approuver une demande
document.querySelectorAll('.approve-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        axios.post(`/admin/operations-cancel/approve/${id}`)
            .then(res => {
                if (res.data.ok) {
                    showToast(res.data.message, 'success');
                    document.querySelector(`#request-${id} td:nth-child(7) span`).className = 'badge bg-success';
                    document.querySelector(`#request-${id} td:nth-child(7) span`).textContent = 'Approuvée';
                    document.querySelector(`#request-${id} td:nth-child(8)`).innerHTML = '-';
                } else {
                    showToast(res.data.message, 'error');
                }
            })
            .catch(() => showToast('Erreur serveur', 'error'));
    });
});

// Rejeter une demande
document.querySelectorAll('.reject-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        axios.post(`/admin/operations-cancel/reject/${id}`)
            .then(res => {
                if (res.data.ok) {
                    showToast(res.data.message, 'success');
                    document.querySelector(`#request-${id} td:nth-child(7) span`).className = 'badge bg-danger';
                    document.querySelector(`#request-${id} td:nth-child(7) span`).textContent = 'Rejetée';
                    document.querySelector(`#request-${id} td:nth-child(8)`).innerHTML = '-';
                } else {
                    showToast(res.data.message, 'error');
                }
            })
            .catch(() => showToast('Erreur serveur', 'error'));
    });
});
</script>
@endsection
