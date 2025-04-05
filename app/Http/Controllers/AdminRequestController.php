<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdminRequest;
use PDF;

class AdminRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = AdminRequest::with('student');
        
        // Filter by status if provided
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        $requests = $query->orderBy('request_date', 'desc')
                        ->paginate(10);
        
        return view('admin.requests.index', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'details' => 'required|string',
            'priority' => 'nullable|string|in:normal,medium,high',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $adminRequest = new AdminRequest();
        $adminRequest->user_id = auth()->id();
        $adminRequest->type = $request->type;
        $adminRequest->details = $request->details;
        $adminRequest->priority = $request->priority ?? 'normal';
        $adminRequest->request_date = now();
        $adminRequest->status = 'pending';
        
        // Handle file upload if attachment exists
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('admin_requests', 'public');
            $adminRequest->attachment = $path;
        }
        
        $adminRequest->save();
        
        return redirect()->route('student.admin-requests.index')
            ->with('success', 'تم تقديم طلبك بنجاح! رقم الطلب: #' . $adminRequest->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $adminRequest = AdminRequest::with('student')->findOrFail($id);
        return view('admin.requests.show', compact('adminRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $adminRequest = AdminRequest::findOrFail($id);
        
        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected',
            'admin_comment' => 'nullable|string|max:500',
        ]);
        
        $adminRequest->status = $request->status;
        $adminRequest->admin_comment = $request->admin_comment;
        $adminRequest->admin_id = auth()->id();
        $adminRequest->save();
        
        return redirect()->route('admin.requests')
                         ->with('success', 'تم تحديث حالة الطلب بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AdminRequest  $adminRequest
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdminRequest $adminRequest)
    {
        // Check if the user owns this request
        if (auth()->id() !== $adminRequest->user_id) {
            return redirect()->route('student.admin-requests.index')
                ->with('error', 'لا يمكنك إلغاء هذا الطلب');
        }
        
        // Only allow cancellation if the request is still pending
        if ($adminRequest->status !== 'pending') {
            return redirect()->route('student.admin-requests.index')
                ->with('error', 'لا يمكن إلغاء الطلب بعد بدء معالجته');
        }
        
        $adminRequest->delete();
        
        return redirect()->route('student.admin-requests.index')
            ->with('success', 'تم إلغاء الطلب بنجاح');
    }

    /**
     * Display a listing of the admin requests for the student.
     *
     * @return \Illuminate\Http\Response
     */
    public function studentIndex()
    {
        $user = auth()->user();
        $requests = $user->adminRequests()
                         ->orderBy('created_at', 'desc')
                         ->paginate(10);
        
        return view('student.admin_requests.index', compact('requests'));
    }

    /**
     * Display the specified admin request for a student.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function studentShow($id)
    {
        $adminRequest = AdminRequest::findOrFail($id);
        
        // Check if the user owns this request
        if (auth()->id() !== $adminRequest->user_id) {
            return redirect()->route('student.requests')
                ->with('error', 'لا يمكنك الوصول إلى هذا الطلب');
        }
        
        return view('student.admin_requests.show', compact('adminRequest'));
    }

    /**
     * Download the certificate for a completed request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function downloadCertificate($id)
    {
        $adminRequest = AdminRequest::findOrFail($id);
        
        // Check if the user owns this request
        if (auth()->id() !== $adminRequest->user_id) {
            return redirect()->route('student.admin-requests.index')
                ->with('error', 'لا يمكنك الوصول إلى هذه الشهادة');
        }
        
        // Check if this is a certificate request and it's completed
        if ($adminRequest->type !== 'certificate_request' || $adminRequest->status !== 'completed') {
            return redirect()->route('student.admin-requests.index')
                ->with('error', 'الشهادة غير متوفرة');
        }
        
        // This is a placeholder. In a real application, you would generate or fetch
        // the certificate file and return it as a download
        $pdf = PDF::loadView('student.admin_requests.certificate', [
            'student' => auth()->user(),
            'request' => $adminRequest,
            'date' => now()->format('Y-m-d'),
        ]);
        
        return $pdf->download('certificate_' . $id . '.pdf');
    }

    /**
     * Show the form for responding to an admin request.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function response($id)
    {
        $request = AdminRequest::with('student')->findOrFail($id);
        return view('admin.requests.responses.edit', compact('request'));
    }
}
