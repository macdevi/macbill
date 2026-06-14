<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', now()->format('Y-m'));

        try {
            $periodDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        } catch (\Throwable $e) {
            $periodDate = now()->startOfMonth();
            $period = $periodDate->format('Y-m');
        }

        $from = $periodDate->copy()->startOfMonth();
        $to = $periodDate->copy()->endOfMonth();

        $query = Expense::with('creator')
            ->whereDate('expense_date', '>=', $from->toDateString())
            ->whereDate('expense_date', '<=', $to->toDateString());

        $totalPeriod = (clone $query)->sum('amount');

        $expenses = (clone $query)
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $todayTotal = Expense::whereDate('expense_date', today())->sum('amount');

        $monthTotal = Expense::whereDate('expense_date', '>=', now()->startOfMonth()->toDateString())
            ->whereDate('expense_date', '<=', now()->endOfMonth()->toDateString())
            ->sum('amount');

        return view('expenses.index', [
            'expenses' => $expenses,
            'totalPeriod' => $totalPeriod,
            'todayTotal' => $todayTotal,
            'monthTotal' => $monthTotal,
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'search' => '',
            'base' => $this->basePath(),
            'home' => $this->homePath(),
        ]);
    }

    public function create()
    {
        $expense = new Expense([
            'expense_date' => today(),
            'payment_method' => 'Tunai',
        ]);

        return view('expenses.form', [
            'expense' => $expense,
            'base' => $this->basePath(),
            'home' => $this->homePath(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['created_by'] = Auth::id();

        Expense::create($data);

        return redirect($this->basePath())->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    public function edit(Expense $expense)
    {
        return view('expenses.form', [
            'expense' => $expense,
            'base' => $this->basePath(),
            'home' => $this->homePath(),
        ]);
    }

    public function update(Request $request, Expense $expense)
    {
        $expense->update($this->validated($request));

        return redirect($this->basePath())->with('success', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect($this->basePath())->with('success', 'Pengeluaran berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'expense_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function basePath(): string
    {
        return request()->is('collector/*') ? '/collector/expenses' : '/admin/expenses';
    }

    private function homePath(): string
    {
        return request()->is('collector/*') ? '/collector/dashboard' : '/admin/dashboard';
    }
}
