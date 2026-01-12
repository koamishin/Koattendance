<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class StudentQrController extends Controller
{
    /**
     * Get the QR code data for a student.
     */
    public function show(Student $student)
    {
        // Ensure the student has a QR code
        if (!$student->qr_code_data) {
            $student->generateQrCode();
        }

        return response()->json([
            'qr_code' => $student->qr_code_data,
            'student_id' => $student->student_id,
            'name' => $student->first_name . ' ' . $student->last_name,
        ]);
    }

    /**
     * Regenerate the QR code for a student.
     */
    public function regenerate(Student $student)
    {
        $qrCode = $student->generateQrCode();

        return response()->json([
            'message' => 'QR code regenerated successfully',
            'qr_code' => $qrCode,
        ]);
    }
    
    /**
     * Decrypt a QR code (for testing/debug purposes, or teacher verification).
     */
    public function validateQr(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        try {
            $data = Crypt::decrypt($request->qr_code);
            return response()->json([
                'valid' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid or corrupted QR code',
            ], 400);
        }
    }
}
