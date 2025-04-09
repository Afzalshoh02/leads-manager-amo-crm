<?php

namespace App\Http\Controllers;

use App\Services\AmoCrmService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request, AmoCrmService $amo)
    {
        $params = $request->all();
        $leadsData = $amo->getLeads($params);
        $pipelines = $amo->getPipelines();

        $leads = array_map(function ($lead) use ($amo, $pipelines) {
            $lead['status_name'] = $amo->getStatusName($lead['status_id'], $pipelines) ?? '—';
            return $lead;
        }, $leadsData['_embedded']['leads'] ?? []);

        return view('leads.index', [
            'leads' => $leads,
            'page' => (int)($params['page'] ?? 1),
            'limit' => (int)($params['limit'] ?? 25),
            'total' => $leadsData['_total'] ?? 0,
        ]);
    }

}
