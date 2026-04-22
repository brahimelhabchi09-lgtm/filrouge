<?php

namespace App\Http\Controllers;

use App\Application\Contract\CreateReportInterface;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private CreateReportInterface $createReport;

    public function __construct(CreateReportInterface $createReport)
    {
        $this->createReport = $createReport;
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "title" => "required",
            "description" => "required",
            "category_id" => "required"
        ]);

        $data = [
            "title" => $request->title,
            "description" => $request->description,
            "category_id" => $request->category_id,
            "student_id" => auth()->user()->id
        ];

        $this->createReport->execute($data);

        session()->flash('success', 'Report created successfully');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
