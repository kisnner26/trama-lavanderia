<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $membership = $request->attributes->get('membership');
        Gate::authorize('receive', $membership);
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:80']], [
            'q.string' => 'escribe una búsqueda válida.', 'q.max' => 'busca con hasta 80 caracteres.',
        ]);
        $search = $validated['q'] ?? '';
        $customers = Customer::where('business_id', $membership->business_id)
            ->when($search !== '', fn (Builder $query): Builder => $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%');
            }))
            ->orderByDesc('id')->paginate(15)->withQueryString();

        return $this->workspaceView($request, 'customers.index', ['customers' => $customers, 'search' => $search]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $membership = $request->attributes->get('membership');
        Customer::create(array_merge($request->validated(), ['business_id' => $membership->business_id]));

        return redirect()->route('customers.index', ['branch' => $membership->branch_id])->with('status', 'cliente guardado en el registro del negocio.');
    }
}
