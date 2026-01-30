<!DOCTYPE html>
<html>

<head>
    <title>Liste des Emprunts - {{ date('d/m/Y') }}</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 10px;
        }

        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 24px;
        }

        .filters {
            background: #f8fafc;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .filters span {
            font-weight: bold;
            color: #4F46E5;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background-color: #4F46E5;
            color: white;
            text-align: left;
            padding: 8px;
            font-weight: bold;
        }

        td {
            border: 1px solid #e2e8f0;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .total-row {
            background-color: #e0e7ff !important;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #10b981;
            color: white;
        }

        .badge-warning {
            background-color: #f59e0b;
            color: white;
        }

        .badge-danger {
            background-color: #ef4444;
            color: white;
        }

        .badge-info {
            background-color: #3b82f6;
            color: white;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Liste des Emprunts</h1>
        <p>Export du {{ date('d/m/Y à H:i') }}</p>
    </div>

    @if (isset($filters) &&
            (isset($filters['search']) || isset($filters['status']) || isset($filters['type_amortissement'])))
        <div class="filters">
            <strong>Filtres appliqués :</strong><br>
            @if (isset($filters['search']) && $filters['search'])
                Recherche : <span>{{ $filters['search'] }}</span>
            @endif
            @if (isset($filters['status']) && $filters['status'])
                | Statut : <span>{{ $filters['status'] }}</span>
            @endif
            @if (isset($filters['type_amortissement']) && $filters['type_amortissement'])
                | Type d'amortissement : <span>{{ $filters['type_amortissement'] }}</span>
            @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                @foreach ($columns as $column)
                    @php
                        $headings = [
                            'reference' => 'Référence',
                            'nom_client' => 'Client',
                            'montant_emprunt' => 'Montant',
                            'date_debut' => 'Date Début',
                            'date_fin_remboursement' => 'Date Fin',
                            'duree_mois' => 'Durée',
                            'taux_interet_annuel' => 'Taux',
                            'type_amortissement' => 'Type',
                            'frequence_paiement' => 'Fréquence',
                            'montant_mensualite' => 'Mensualité',
                            'total_interets' => 'Intérêts',
                            'total_a_rembourser' => 'Total à Remb.',
                            'status' => 'Statut',
                        ];
                    @endphp
                    <th>{{ $headings[$column] ?? ucfirst(str_replace('_', ' ', $column)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($emprunts as $emprunt)
                <tr>
                    @foreach ($columns as $column)
                        <td>
                            @switch($column)
                                @case('montant_emprunt')
                                @case('montant_mensualite')

                                @case('total_interets')
                                @case('total_a_rembourser')
                                    {{ number_format($emprunt->{$column}, 0, ',', ' ') }} FCFA
                                @break

                                @case('date_debut')
                                @case('date_fin_remboursement')
                                    {{ $emprunt->{$column}->format('d/m/Y') }}
                                @break

                                @case('taux_interet_annuel')
                                    {{ number_format($emprunt->{$column}, 2, ',', ' ') }}%
                                @break

                                @case('status')
                                    @php
                                        $badgeClass = match ($emprunt->status) {
                                            'approuve' => 'badge-success',
                                            'en_attente' => 'badge-warning',
                                            'rejete' => 'badge-danger',
                                            'termine' => 'badge-info',
                                            default => '',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $emprunt->status)) }}
                                    </span>
                                @break

                                @case('type_amortissement')
                                    {{ ucfirst(str_replace('_', ' ', $emprunt->type_amortissement)) }}
                                @break

                                @case('frequence_paiement')
                                    {{ ucfirst($emprunt->frequence_paiement) }}
                                @break

                                @default
                                    {{ $emprunt->{$column} ?? '' }}
                            @endswitch
                        </td>
                    @endforeach
                </tr>
            @endforeach

            <!-- Ligne des totaux -->
            <tr class="total-row">
                <td colspan="{{ count($columns) - 5 }}" style="text-align: right; border-right: none;"><strong>TOTAUX
                        :</strong></td>
                <td><strong>{{ number_format($emprunts->sum('montant_emprunt'), 0, ',', ' ') }} FCFA</strong></td>
                <td></td>
                <td></td>
                <td><strong>{{ number_format($emprunts->sum('total_interets'), 0, ',', ' ') }} FCFA</strong></td>
                <td><strong>{{ number_format($emprunts->sum('total_a_rembourser'), 0, ',', ' ') }} FCFA</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Généré par {{ auth()->user()->name ?? 'Système' }} • {{ count($emprunts) }} emprunt(s) • Page 1/1</p>
    </div>
</body>

</html>
