<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TaxBracket;
use App\Services\TaxCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxBracketController extends Controller
{
    protected $taxService;

    public function __construct(TaxCalculationService $taxService)
    {
        $this->taxService = $taxService;
    }

    /**
     * Display a listing of tax brackets
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = TaxBracket::query();
        
        // Filter by active status
        if ($request->has('active_only') && $request->active_only) {
            $query->where('is_active', true);
        }
        
        // Filter by date
        if ($request->has('date')) {
            $query->forDate($request->date);
        }
        
        $taxBrackets = $query->ordered()->get();
        
        return view('tax-brackets.index', compact('taxBrackets', 'user'));
    }

    /**
     * Show the form for creating a new tax bracket
     */
    public function create()
    {
        $user = Auth::user();
        return view('tax-brackets.form', compact('user'));
    }

    /**
     * Store a newly created tax bracket
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'min_income' => 'required|numeric|min:0',
            'max_income' => 'nullable|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'base_tax' => 'required|numeric|min:0',
            'excess_over' => 'required|numeric|min:0',
            'sort_order' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from',
        ]);

        TaxBracket::create($request->validated());

        return redirect()->route('tax-brackets.index')
            ->with('success', 'Tax bracket created successfully.');
    }

    /**
     * Display the specified tax bracket
     */
    public function show(TaxBracket $taxBracket)
    {
        $user = Auth::user();
        return view('tax-brackets.show', compact('taxBracket', 'user'));
    }

    /**
     * Show the form for editing the specified tax bracket
     */
    public function edit(TaxBracket $taxBracket)
    {
        $user = Auth::user();
        return view('tax-brackets.form', compact('taxBracket', 'user'));
    }

    /**
     * Update the specified tax bracket
     */
    public function update(Request $request, TaxBracket $taxBracket)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'min_income' => 'required|numeric|min:0',
            'max_income' => 'nullable|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'base_tax' => 'required|numeric|min:0',
            'excess_over' => 'required|numeric|min:0',
            'sort_order' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from',
        ]);

        $taxBracket->update($request->validated());

        return redirect()->route('tax-brackets.index')
            ->with('success', 'Tax bracket updated successfully.');
    }

    /**
     * Remove the specified tax bracket
     */
    public function destroy(TaxBracket $taxBracket)
    {
        $taxBracket->delete();

        return redirect()->route('tax-brackets.index')
            ->with('success', 'Tax bracket deleted successfully.');
    }

    /**
     * Calculate tax for a given income
     */
    public function calculateTax(Request $request)
    {
        $request->validate([
            'income' => 'required|numeric|min:0',
            'date' => 'nullable|date'
        ]);

        $income = $request->income;
        $date = $request->date;

        $taxAmount = $this->taxService->calculateTax($income, $date);
        $breakdown = $this->taxService->getTaxBreakdown($income, $date);

        return response()->json([
            'income' => $income,
            'tax_amount' => $taxAmount,
            'breakdown' => $breakdown
        ]);
    }

    /**
     * Create Philippine tax brackets
     */
    public function createPhilippineBrackets()
    {
        $this->taxService->createPhilippineTaxBrackets();
        
        return redirect()->route('tax-brackets.index')
            ->with('success', 'Philippine tax brackets created successfully.');
    }
}
