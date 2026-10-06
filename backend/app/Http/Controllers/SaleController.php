<?php

namespace App\Http\Controllers;

use App\Enums\BillingUnit;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $membership = $request->attributes->get('membership');
        Gate::authorize('receive', $membership);
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:80']], ['q.string' => 'escribe una búsqueda válida.', 'q.max' => 'usa hasta 80 caracteres.']);
        $search = $filters['q'] ?? '';
        $sales = Sale::where('business_id', $membership->business_id)->where('branch_id', $membership->branch_id)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('customer_name', 'like', '%'.$search.'%');
                    if (preg_match('/^(?:tr-)?0*([0-9]+)$/', $search, $match)) {
                        $query->orWhere('id', $match[1]);
                    }
                });
            })->withSum('payments', 'amount_minor')->orderByDesc('id')->paginate(15)->withQueryString();

        return $this->workspaceView($request, 'sales.index', ['sales' => $sales, 'search' => $search]);
    }

    public function create(Request $request): View
    {
        $membership = $request->attributes->get('membership');
        Gate::authorize('receive', $membership);

        return $this->workspaceView($request, 'sales.create', [
            'customers' => Customer::where('business_id', $membership->business_id)->orderBy('name')->get(),
            'services' => Service::where('business_id', $membership->business_id)->orderBy('name')->get(),
            'requestKey' => (string) Str::uuid(),
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $membership = $request->attributes->get('membership');
        $data = $request->validated();
        $normalizedLines = [];
        foreach ($data['lines'] as $index => $line) {
            [$whole,$fraction] = array_pad(explode('.', $line['quantity']), 2, '0');
            $quantity = (int) $whole * 1000 + (int) str_pad($fraction, 3, '0', STR_PAD_RIGHT);
            if ($quantity < 1) {
                throw ValidationException::withMessages(["lines.$index.quantity" => 'la cantidad debe ser mayor que cero.']);
            }
            $normalizedLines[] = ['service_id' => (int) $line['service_id'], 'quantity_milli' => $quantity];
        }
        $hash = hash('sha256', json_encode(['customer_id' => (int) $data['customer_id'], 'notes' => $data['notes'] ?? null, 'lines' => $normalizedLines], JSON_THROW_ON_ERROR));
        $sale = DB::transaction(function () use ($membership, $data, $normalizedLines, $hash): Sale {
            Branch::whereKey($membership->branch_id)->lockForUpdate()->firstOrFail();
            $existing = Sale::where('business_id', $membership->business_id)->where('branch_id', $membership->branch_id)->where('request_key', $data['request_key'])->first();
            if ($existing) {
                abort_unless(hash_equals($existing->request_hash, $hash), 409, 'esta solicitud ya se utilizó para otra venta.');

                return $existing;
            }
            $customer = Customer::where('business_id', $membership->business_id)->whereKey($data['customer_id'])->first();
            if (! $customer) {
                throw ValidationException::withMessages(['customer_id' => 'elige un cliente de este negocio.']);
            }
            $lines = [];
            $total = 0;
            foreach ($normalizedLines as $index => $line) {
                $service = Service::where('business_id', $membership->business_id)->whereKey($line['service_id'])->sharedLock()->first();
                if (! $service) {
                    throw ValidationException::withMessages(["lines.$index.service_id" => 'elige un servicio de este negocio.']);
                }
                if ($service->billing_unit === BillingUnit::Piece && $line['quantity_milli'] % 1000 !== 0) {
                    throw ValidationException::withMessages(["lines.$index.quantity" => 'las piezas se cuentan con números enteros.']);
                }
                $amount = intdiv($service->price_minor * $line['quantity_milli'] + 500, 1000);
                $total += $amount;
                $lines[] = ['business_id' => $membership->business_id, 'service_id' => $service->id, 'service_name' => $service->name, 'billing_unit' => $service->billing_unit->value, 'quantity_milli' => $line['quantity_milli'], 'price_minor' => $service->price_minor, 'total_minor' => $amount, 'requires_finish' => $service->requires_finish];
            }
            $sale = Sale::create(['business_id' => $membership->business_id, 'branch_id' => $membership->branch_id, 'customer_id' => $customer->id, 'created_by' => $membership->user_id, 'request_key' => $data['request_key'], 'request_hash' => $hash, 'customer_name' => $customer->name, 'customer_phone' => $customer->phone, 'business_name' => $membership->branch->business->name, 'branch_name' => $membership->branch->name, 'currency' => $membership->branch->business->currency, 'timezone' => $membership->branch->business->timezone, 'total_minor' => $total, 'notes' => $data['notes'] ?? null]);
            $sale->lines()->createMany($lines);
            $sale->events()->create(['business_id' => $sale->business_id, 'user_id' => $membership->user_id, 'action' => 'issued', 'details' => 'venta emitida']);

            return $sale;
        }, 3);

        return redirect()->route('sales.show', ['branch' => $membership->branch_id, 'sale' => $sale->id])->with('status', 'venta emitida. ya puedes preparar el recibo.');
    }

    public function show(Request $request, string $branch, string $sale): View
    {
        $record = $this->scopedSale($request, $sale)->load('lines', 'payments', 'events.actor');

        return $this->workspaceView($request, 'sales.show', ['sale' => $record, 'paid' => $record->paidMinor(), 'requestKey' => (string) Str::uuid()]);
    }

    public function payment(StorePaymentRequest $request, string $branch, string $sale): RedirectResponse
    {
        $record = $this->scopedSale($request, $sale);
        $data = $request->validated();
        [$whole,$fraction] = array_pad(explode('.', $data['amount']), 2, '0');
        $amount = (int) $whole * 100 + (int) str_pad($fraction, 2, '0', STR_PAD_RIGHT);
        DB::transaction(function () use ($record, $request, $data, $amount): void {
            $record = Sale::whereKey($record->id)->lockForUpdate()->firstOrFail();
            $existing = $record->payments()->where('request_key', $data['request_key'])->first();
            if ($existing) {
                abort_unless((int) $existing->amount_minor === $amount && $existing->method === $data['method'] && $existing->reference === ($data['reference'] ?? null), 409, 'esta solicitud ya se utilizó para otro pago.');

                return;
            }
            if ($amount < 1 || $amount > $record->total_minor - $record->paidMinor()) {
                throw ValidationException::withMessages(['amount' => 'el importe debe ser positivo y no superar el saldo.']);
            }
            $record->payments()->create(['business_id' => $record->business_id, 'created_by' => $request->user()->id, 'request_key' => $data['request_key'], 'amount_minor' => $amount, 'method' => $data['method'], 'reference' => $data['reference'] ?? null]);
            $record->events()->create(['business_id' => $record->business_id, 'user_id' => $request->user()->id, 'action' => 'payment', 'details' => 'pago registrado / '.Sale::money($amount).' '.$record->currency]);
        }, 3);

        return redirect()->route('sales.show', ['branch' => $branch, 'sale' => $record->id])->with('status', 'pago registrado en el historial.');
    }

    public function prepareReceipt(Request $request, string $branch, string $sale): RedirectResponse
    {
        $record = $this->scopedSale($request, $sale);
        $record->events()->create(['business_id' => $record->business_id, 'user_id' => $request->user()->id, 'action' => 'receipt', 'details' => 'comprobante preparado para imprimir']);

        return redirect()->route('sales.receipt', ['branch' => $branch, 'sale' => $record->id]);
    }

    public function receipt(Request $request, string $branch, string $sale): View
    {
        $record = $this->scopedSale($request, $sale)->load('lines', 'payments');

        return view('sales.receipt', ['sale' => $record, 'paid' => $record->paidMinor(), 'branch' => $branch]);
    }

    private function scopedSale(Request $request, string $id): Sale
    {
        $membership = $request->attributes->get('membership');
        Gate::authorize('receive', $membership);

        return Sale::where('business_id', $membership->business_id)->where('branch_id', $membership->branch_id)->whereKey($id)->firstOrFail();
    }
}
