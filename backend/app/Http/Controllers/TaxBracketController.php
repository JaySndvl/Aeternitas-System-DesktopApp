<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaxBracket;
use App\Services\TaxCalculationService;

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
    public function index()
    {
        $taxBrackets = TaxBracket::active()->ordered()->get();
        return response()->json($taxBrackets);
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
     * Get tax calculation for a range of incomes
     */
    public function calculateRange(Request $request)
    {
        $request->validate([
            'start_income' => 'required|numeric|min:0',
            'end_income' => 'required|numeric|min:0',
            'step' => 'nullable|numeric|min:100',
            'date' => 'nullable|date'
        ]);

        $startIncome = $request->start_income;
        $endIncome = $request->end_income;
        $step = $request->step ?? 1000;
        $date = $request->date;

        $results = $this->taxService->calculateTaxForRange($startIncome, $endIncome, $step, $date);

        return response()->json($results);
    }

    /**
     * Get all active tax brackets
     */
    public function getActiveBrackets(Request $request)
    {
        $date = $request->date;
        $brackets = $this->taxService->getActiveTaxBrackets($date);

        return response()->json($brackets);
    }

    /**
     * Create or update Philippine tax brackets
     */
    public function createPhilippineBrackets()
    {
        $this->taxService->createPhilippineTaxBrackets();
        
        return response()->json([
            'message' => 'Philippine tax brackets created successfully'
        ]);
    }
}
