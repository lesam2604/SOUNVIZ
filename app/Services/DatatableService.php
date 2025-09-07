<?php

namespace App\Services;

class DatatableService
{
    public function fetch($dtParams)
    {
        // Count
        $recordsFiltered = $recordsTotal = with(clone $dtParams['builder'])->count();

        // Search
        if ($search = $dtParams['search']['value'] ?? '') {
            $dtParams['builder']->where(function ($q) use ($dtParams, $search) {
                $q->whereRaw('0');

                foreach ($dtParams['columns'] as $column) {
                    if ($column['searchable'] === 'true') {
                        $q->orWhere($column['name'], 'LIKE', '%' . $search . '%');
                    }
                }
            });

            $recordsFiltered = with(clone $dtParams['builder'])->count();
        }

        // Order
        foreach ($dtParams['order'] ?? [] as $order) {
            $column = $dtParams['columns'][$order['column']];

            if ($column['orderable'] === 'true') {
                $dtParams['builder']->orderBy($column['name'], $order['dir']);
            }
        }

        // Limit
        if ($dtParams['length'] !== -1) {
            $dtParams['builder']->offset($dtParams['start'])->limit($dtParams['length']);
        }

        $data = $dtParams['builder']->get();

        // Add line numbers
        foreach ($data as $key => $value) {
            if ($dtParams['order'][0]['dir'] === 'asc') {
                $value->__no__ = intval($dtParams['start']) + $key + 1;
            } else {
                $value->__no__ = $recordsFiltered - intval($dtParams['start']) - $key;
            }
        }

        // Apply callback
        if (isset($dtParams['rowsCallback'])) {
            $data = $data->map($dtParams['rowsCallback']);
        }

        return [
            'draw' => intval($dtParams['draw']),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }
}
