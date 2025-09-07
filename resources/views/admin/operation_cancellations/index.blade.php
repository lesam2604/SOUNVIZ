@extends('layouts.app') 

@section('title', 'Demandes d\'annulation d\'opérations')

@section('content')
<div class="container mt-4">
    <h2>Demandes d'annulation d'opérations</h2>

    <div id="alert-placeholder"></div>

    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Collaborateur</th>
                <th>Opération</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $request)
            <tr id="request-row-{{ $request->id }}">
                <td>{{ $request->id }}</td>
                <td>
                    {{ $request->collaborator?->first_name }} {{ $request->collaborator?->last_name }}
                    <br>
                    <small>{{ $request->collaborator?->email }}</small>
                </td>
                <td>
                    {{ $request->operation?->op_type_code ?? 'N/A' }}
                </td>
                <td>{{ number_format($request->amount, 0, ',', ' ') }} FCFA</td>
                <td id="status-{{ $request->id }}">
                    @if($request->status === 'pending')
                        <span class="badge bg-warning text-dark">En attente</span>
                    @elseif($request->status === 'approved')
                        <span class="badge bg-success">Approuvée</span>
                    @else
                        <span class="badge bg-danger">Rejetée</span>
                    @endif
                </td>
                <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @if($request->status === 'pending')
                        <button class="btn btn-success btn-sm approve-btn" data-id="{{ $request->id }}">Approuver</button>
                        <button class="btn btn-danger btn-sm reject-btn" data-id="{{ $request->id }}">Rejeter</button>
                    @else
                        <em>Aucune action</em>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    function showAlert(message, type = 'success') {
        const html = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        document.getElementById('alert-placeholder').innerHTML = html;
    }

    // Approve
    document.querySelectorAll('.approve-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`/admin/operations-cancel/approve/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.ok){
                    document.getElementById(`status-${id}`).innerHTML = '<span class="badge bg-success">Approuvée</span>';
                    document.getElementById(`request-row-${id}`).querySelector('td:last-child').innerHTML = '<em>Aucune action</em>';
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(err => showAlert('Erreur serveur', 'danger'));
        });
    });

    // Reject
    document.querySelectorAll('.reject-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            fetch(`/admin/operations-cancel/reject/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.ok){
                    document.getElementById(`status-${id}`).innerHTML = '<span class="badge bg-danger">Rejetée</span>';
                    document.getElementById(`request-row-${id}`).querySelector('td:last-child').innerHTML = '<em>Aucune action</em>';
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(err => showAlert('Erreur serveur', 'danger'));
        });
    });

});
</script>
@endsection
