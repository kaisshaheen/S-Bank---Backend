<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatementRequest;
use App\Services\Statement\MonthlyStatementService;
use App\Services\Statement\StatementService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatementController extends Controller
{
    
    public function __construct(private StatementService $statementService , private MonthlyStatementService $monthly)
    {
        //
    }

    public function index(StatementRequest $request){
        $filters = $request->validated();

        $statement = $this->statementService->handle(Auth::user()->account , $filters , $request);

        return response()->json([
            'statement' => $statement
        ] , 200);
    }

    public function monthlyPdf(Request $request){

        $request->validate([
            'month'=>'required|numeric|min:1|max:12',
            'year'=>'required|numeric'
        ]);

        $pdf = $this->monthly->generate(Auth::user()->account , $request->month , $request->year);

        return response($pdf,200)
        ->header('Content-Type','application/pdf')
        ->header('Content-Disposition','inline; filename="statement.pdf"');

    }

}
