<?php

namespace App\Http\Controllers;

use App\Enums\BillingUnit;
use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $membership = $request->attributes->get('membership');
        Gate::authorize('receive', $membership);
        $services = Service::where('business_id', $membership->business_id)->orderByDesc('id')->paginate(15);

        return $this->workspaceView($request, 'services.index', ['services' => $services, 'billingUnits' => BillingUnit::cases()]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $membership = $request->attributes->get('membership');
        $data = $request->validated();
        [$whole, $fraction] = array_pad(explode('.', $data['price']), 2, '0');
        Service::create([
            'business_id' => $membership->business_id,
            'name' => $data['name'], 'billing_unit' => $data['billing_unit'],
            'price_minor' => ((int) $whole * 100) + (int) str_pad($fraction, 2, '0', STR_PAD_RIGHT),
            'requires_finish' => $data['requires_finish'],
        ]);

        return redirect()->route('services.index', ['branch' => $membership->branch_id])->with('status', 'servicio guardado en el tarifario del negocio.');
    }
}
